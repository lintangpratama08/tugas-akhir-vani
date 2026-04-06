-- Adminer 4.8.1 PostgreSQL 15.6 dump

CREATE SEQUENCE peta_ogc_fid_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 START 38 CACHE 1;

CREATE TABLE "public"."peta" (
    "ogc_fid" integer DEFAULT nextval('peta_ogc_fid_seq') NOT NULL,
    "wkb_geometry" geometry(MultiPolygon,4326),
    "adm0_en" character varying(50),
    "date" date,
    "validon" date,
    "province" character varying(50),
    "kabupaten" character varying(50),
    "prv2" character varying(50),
    CONSTRAINT "peta_pk" PRIMARY KEY ("ogc_fid")
) WITH (oids = false);

CREATE INDEX "peta_wkb_geometry_geom_idx" ON "public"."peta" USING btree ("wkb_geometry");


CREATE TABLE "public"."tabel_pad" (
    "akun" character varying(512),
    "anggaran" numeric,
    "realisasi" numeric,
    "persentase" numeric,
    "kota" integer,
    "tahun" integer
) WITH (oids = false);


-- 2026-04-05 21:00:12.377984+07