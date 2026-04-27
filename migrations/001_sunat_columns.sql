-- 001_sunat_columns.sql
-- Añade columnas SUNAT a la tabla `pagos` (que ya contiene tipo_comprobante,
-- ruc_cliente y razon_social). Solo aplica a comprobantes 'boleta' o 'factura'.

ALTER TABLE pagos
  ADD COLUMN serie_doc      VARCHAR(4)   NULL AFTER razon_social,
  ADD COLUMN num_doc        VARCHAR(20)  NULL AFTER serie_doc,
  ADD COLUMN sunat_estado   ENUM('pendiente','aceptado','rechazado') NULL AFTER num_doc,
  ADD COLUMN sunat_hash     VARCHAR(255) NULL AFTER sunat_estado,
  ADD COLUMN sunat_qr       TEXT         NULL AFTER sunat_hash,
  ADD COLUMN sunat_xml      LONGTEXT     NULL AFTER sunat_qr,
  ADD COLUMN sunat_cdr      LONGTEXT     NULL AFTER sunat_xml,
  ADD COLUMN sunat_mensaje  VARCHAR(1000) NULL AFTER sunat_cdr,
  ADD COLUMN sunat_fecha    DATETIME     NULL AFTER sunat_mensaje,
  ADD INDEX idx_pagos_sunat_estado (sunat_estado),
  ADD INDEX idx_pagos_serie_num   (serie_doc, num_doc);
