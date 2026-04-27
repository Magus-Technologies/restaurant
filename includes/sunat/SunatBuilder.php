<?php
/**
 * SunatBuilder — Construye el payload JSON que la API Laravel espera.
 *
 * Convierte los datos del dominio RestaurantOS (pago + orden + cliente + items)
 * al formato que pide GenerarComprobanteRequest.
 */
class SunatBuilder
{
    /**
     * @param array $pago    Fila de `pagos` con tipo_comprobante, serie_doc, num_doc, ruc_cliente, razon_social.
     * @param array $cliente Fila de `clientes` (dni, ruc, razon_social, nombre, apellido, direccion). Puede venir vacío.
     * @param array $items   Filas de `orden_detalle` con prod_nombre/cantidad/precio_unitario.
     * @param string $fechaEmision Fecha del pago (created_at).
     */
    public static function buildComprobante(array $pago, array $cliente, array $items, string $fechaEmision): array
    {
        $tipo = $pago['tipo_comprobante']; // 'boleta' | 'factura'

        return [
            'endpoint'      => SUNAT_ENDPOINT,
            'documento'     => $tipo,
            'empresa'       => self::empresa(),
            'cliente'       => self::cliente($pago, $cliente, $tipo),
            'serie'         => $pago['serie_doc'],
            'numero'        => (string) $pago['num_doc'],
            'fecha_emision' => $fechaEmision,
            'moneda'        => 'PEN',
            'forma_pago'    => 'contado',
            'detalles'      => self::detalles($items),
        ];
    }

    private static function empresa(): array
    {
        return [
            'ruc'             => SUNAT_RUC,
            'usuario'         => SUNAT_USUARIO_SOL,
            'clave'           => SUNAT_CLAVE_SOL,
            'razon_social'    => SUNAT_RAZON_SOCIAL,
            'nombreComercial' => SUNAT_NOMBRE_COMERCIAL,
            'direccion'       => SUNAT_DIRECCION,
            'ubigueo'         => SUNAT_UBIGEO,
            'distrito'        => SUNAT_DISTRITO,
            'provincia'       => SUNAT_PROVINCIA,
            'departamento'    => SUNAT_DEPARTAMENTO,
        ];
    }

    /**
     * Para `factura`: requiere RUC. Toma `pago.ruc_cliente` + `pago.razon_social`
     * (el cajero los capturó en el cobro), o cae al cliente vinculado.
     *
     * Para `boleta`: usa DNI si lo hay, si no genera "varios" con tipo_doc=0.
     */
    private static function cliente(array $pago, array $c, string $tipo): array
    {
        $rucPago = trim((string)($pago['ruc_cliente'] ?? ''));
        $rsPago  = trim((string)($pago['razon_social'] ?? ''));
        $dirCli  = trim((string)($c['direccion'] ?? '-')) ?: '-';

        if ($tipo === 'factura') {
            $ruc = $rucPago !== '' ? $rucPago : trim((string)($c['ruc'] ?? ''));
            $rs  = $rsPago !== '' ? $rsPago  : trim((string)($c['razon_social'] ?? ''));
            if (strlen($ruc) !== 11) {
                throw new RuntimeException("La factura requiere RUC válido de 11 dígitos. RUC actual: '$ruc'.");
            }
            if ($rs === '') {
                throw new RuntimeException("La factura requiere razón social.");
            }
            return ['tipo_doc' => '6', 'num_doc' => $ruc, 'rzn_social' => $rs, 'direccion' => $dirCli];
        }

        // Boleta
        $dni = trim((string)($c['dni'] ?? ''));
        $nom = trim(trim((string)($c['nombre'] ?? '')) . ' ' . trim((string)($c['apellido'] ?? '')));
        if ($nom === '') $nom = $rsPago !== '' ? $rsPago : 'CLIENTE VARIOS';

        if (strlen($dni) === 8) {
            return ['tipo_doc' => '1', 'num_doc' => $dni, 'rzn_social' => $nom, 'direccion' => $dirCli];
        }
        return ['tipo_doc' => '0', 'num_doc' => '00000000', 'rzn_social' => $nom, 'direccion' => $dirCli];
    }

    /**
     * `precio_unitario` se asume CON IGV incluido (el servicio Greenter divide
     * entre 1.18 internamente).
     */
    private static function detalles(array $items): array
    {
        $out = [];
        foreach ($items as $i => $it) {
            $out[] = [
                'cod_producto' => (string) ($it['id_plato'] ?? ($i + 1)),
                'unidad'       => 'NIU',
                'descripcion'  => $it['plato_nombre'] ?? 'Producto',
                'cantidad'     => (float) ($it['cantidad'] ?? 1),
                'precio'       => (float) ($it['precio_unitario'] ?? 0),
            ];
        }
        return $out;
    }
}
