<?php
/**
 * SunatService — Orquesta el flujo de facturación electrónica en DOS pasos.
 *
 *   1) generarXml($pagoId)   → llama /generar/comprobante, guarda XML+hash+qr,
 *                              deja sunat_estado = 'pendiente'.
 *   2) enviarSunat($pagoId)  → toma el XML guardado, llama /enviar/documento/electronico,
 *                              guarda CDR, deja sunat_estado = 'aceptado' | 'rechazado'.
 *
 * Opera sobre la tabla `pagos` (que es el comprobante en RestaurantOS).
 */
require_once __DIR__ . '/SunatClient.php';
require_once __DIR__ . '/SunatBuilder.php';

class SunatService
{
    private PDO          $db;
    private SunatClient  $client;

    public function __construct(?PDO $db = null, ?SunatClient $client = null)
    {
        $this->db     = $db ?? DB::getPdo();
        $this->client = $client ?? new SunatClient();
    }

    // ─── PASO 1: GENERAR XML ──────────────────────────────────────
    public function generarXml(int $pagoId): array
    {
        $pago = $this->fetchPago($pagoId);
        if (!$pago) {
            return ['ok' => false, 'mensaje' => "Pago #$pagoId no encontrado."];
        }
        if (!in_array($pago['tipo_comprobante'], ['factura', 'boleta'], true)) {
            return ['ok' => false, 'mensaje' => "Tipo '{$pago['tipo_comprobante']}' no se emite a SUNAT."];
        }
        if (empty($pago['serie_doc']) || empty($pago['num_doc'])) {
            return ['ok' => false, 'mensaje' => 'El pago no tiene serie/numero asignados.'];
        }

        // Cliente: por id_orden buscamos un id_cliente si existe en algún lado;
        // RestaurantOS guarda RUC y razón social en el propio `pagos`, así que
        // nos basta con eso. El cliente en `clientes` solo se usa si está vinculado.
        $cliente = $this->fetchClienteDelPago($pagoId);
        $items   = $this->fetchItems((int) $pago['id_orden']);
        $fecha   = $pago['created_at'] ?? date('Y-m-d H:i:s');

        try {
            $payload = SunatBuilder::buildComprobante($pago, $cliente, $items, $fecha);
        } catch (Throwable $e) {
            $this->marcarRechazada($pagoId, $e->getMessage());
            return ['ok' => false, 'mensaje' => $e->getMessage()];
        }

        $gen = $this->client->generarComprobante($payload);
        if (empty($gen['estado'])) {
            $msg = $gen['mensaje'] ?? 'Error al generar XML.';
            $this->marcarRechazada($pagoId, $msg);
            return ['ok' => false, 'mensaje' => $msg, 'detalle' => $gen];
        }

        $hash   = $gen['data']['hash']          ?? '';
        $qrInfo = $gen['data']['qr_info']       ?? '';
        $xml    = $gen['data']['contenido_xml'] ?? '';

        $this->marcarPendiente($pagoId, $hash, $qrInfo, $xml);

        return [
            'ok'      => true,
            'mensaje' => 'XML generado correctamente. Listo para enviar a SUNAT.',
            'hash'    => $hash,
            'qr'      => $qrInfo,
        ];
    }

    // ─── PASO 2: ENVIAR A SUNAT ───────────────────────────────────
    public function enviarSunat(int $pagoId): array
    {
        $pago = $this->fetchPago($pagoId);
        if (!$pago) {
            return ['ok' => false, 'mensaje' => "Pago #$pagoId no encontrado."];
        }
        if (empty($pago['sunat_xml'])) {
            return ['ok' => false, 'mensaje' => 'Este pago no tiene XML generado todavía.'];
        }
        if ($pago['sunat_estado'] === 'aceptado') {
            return ['ok' => false, 'mensaje' => 'Este pago ya fue aceptado por SUNAT.'];
        }

        $nombreArchivo = self::nombreArchivo($pago);

        $env = $this->client->enviarDocumento([
            'ruc'                 => SUNAT_RUC,
            'usuario'             => SUNAT_USUARIO_SOL,
            'clave'               => SUNAT_CLAVE_SOL,
            'endpoint'            => SUNAT_ENDPOINT,
            'nombre_documento'    => $nombreArchivo,
            'contenido_documento' => $pago['sunat_xml'],
        ]);

        if (empty($env['estado'])) {
            $msg = $env['mensaje'] ?? 'Error al enviar a SUNAT.';
            $this->marcarRechazada(
                $pagoId, $msg,
                $pago['sunat_hash'] ?? '',
                $pago['sunat_qr']   ?? '',
                $pago['sunat_xml']  ?? ''
            );
            return ['ok' => false, 'mensaje' => $msg, 'detalle' => $env];
        }

        $this->marcarAceptada(
            $pagoId,
            $pago['sunat_hash'] ?? '',
            $pago['sunat_qr']   ?? '',
            $pago['sunat_xml']  ?? '',
            $env['cdr']     ?? '',
            $env['mensaje'] ?? 'ACEPTADO'
        );

        return [
            'ok'      => true,
            'mensaje' => 'Comprobante aceptado por SUNAT.',
            'cdr'     => $env['cdr'] ?? '',
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public static function nombreArchivo(array $pago): string
    {
        $tipo = $pago['tipo_comprobante'] === 'factura' ? '01' : '03';
        $num  = str_pad((string) $pago['num_doc'], 8, '0', STR_PAD_LEFT);
        return SUNAT_RUC . '-' . $tipo . '-' . $pago['serie_doc'] . '-' . $num;
    }

    /**
     * Próximo correlativo libre para una serie dada en `pagos`.
     */
    public static function siguienteNumero(PDO $db, string $serie): int
    {
        $st = $db->prepare("SELECT COALESCE(MAX(CAST(num_doc AS UNSIGNED)),0)+1 FROM pagos WHERE serie_doc=?");
        $st->execute([$serie]);
        return (int) $st->fetchColumn();
    }

    // ─── Persistencia ────────────────────────────────────────────────

    private function marcarPendiente(int $id, string $hash, string $qr, string $xml): void
    {
        $st = $this->db->prepare("
            UPDATE pagos SET
                sunat_estado='pendiente',
                sunat_hash=?,
                sunat_qr=?,
                sunat_xml=?,
                sunat_cdr=NULL,
                sunat_mensaje='XML generado, pendiente de envío.',
                sunat_fecha=NOW()
            WHERE id=?
        ");
        $st->execute([$hash, $qr, $xml, $id]);
    }

    private function marcarAceptada(int $id, string $hash, string $qr, string $xml, string $cdr, string $msg): void
    {
        $st = $this->db->prepare("
            UPDATE pagos SET
                sunat_estado='aceptado',
                sunat_hash=?,
                sunat_qr=?,
                sunat_xml=?,
                sunat_cdr=?,
                sunat_mensaje=?,
                sunat_fecha=NOW()
            WHERE id=?
        ");
        $st->execute([$hash, $qr, $xml, $cdr, $msg, $id]);
    }

    private function marcarRechazada(int $id, string $msg, string $hash = '', string $qr = '', string $xml = ''): void
    {
        $st = $this->db->prepare("
            UPDATE pagos SET
                sunat_estado='rechazado',
                sunat_hash=?,
                sunat_qr=?,
                sunat_xml=?,
                sunat_mensaje=?,
                sunat_fecha=NOW()
            WHERE id=?
        ");
        $st->execute([$hash, $qr, $xml, mb_substr($msg, 0, 1000), $id]);
    }

    // ─── Lecturas ────────────────────────────────────────────────────

    private function fetchPago(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM pagos WHERE id=?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    /**
     * RestaurantOS no enlaza directamente `pagos` con `clientes`. Buscamos por
     * coincidencia de RUC o DNI almacenado en el pago. Si no hay match, retorna [].
     */
    private function fetchClienteDelPago(int $pagoId): array
    {
        $st = $this->db->prepare("SELECT ruc_cliente FROM pagos WHERE id=?");
        $st->execute([$pagoId]);
        $ruc = trim((string) $st->fetchColumn());
        if ($ruc === '') return [];

        $st = $this->db->prepare("SELECT * FROM clientes WHERE ruc=? OR dni=? LIMIT 1");
        $st->execute([$ruc, $ruc]);
        $row = $st->fetch();
        return $row ?: [];
    }

    private function fetchItems(int $ordenId): array
    {
        $st = $this->db->prepare("
            SELECT od.id, od.id_plato, od.cantidad, od.precio_unitario,
                   p.nombre AS plato_nombre
            FROM orden_detalle od
            JOIN platos p ON p.id = od.id_plato
            WHERE od.id_orden=?
              AND od.estado <> 'cancelado'
            ORDER BY od.id
        ");
        $st->execute([$ordenId]);
        return $st->fetchAll();
    }
}
