-- Create sequence "bugs_id_seq"
CREATE SEQUENCE "public"."bugs_id_seq";
-- Create sequence "users_id_seq"
CREATE SEQUENCE "public"."users_id_seq";
-- Create "users" table
CREATE TABLE "public"."users" (
  "id" integer NOT NULL,
  "name" character varying(255) NOT NULL,
  PRIMARY KEY ("id")
);
-- Create "bugs" table
CREATE TABLE "public"."bugs" (
  "id" integer NOT NULL,
  "engineer_id" integer NULL,
  "reporter_id" integer NULL,
  "description" character varying(255) NOT NULL,
  "created" timestamp(0) NOT NULL,
  "status" character varying(255) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "fk_1e197c9e1cfe6f5" FOREIGN KEY ("reporter_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT "fk_1e197c9f8d8cdf1" FOREIGN KEY ("engineer_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION
);
-- Create index "idx_1e197c9e1cfe6f5" to table: "bugs"
CREATE INDEX "idx_1e197c9e1cfe6f5" ON "public"."bugs" ("reporter_id");
-- Create index "idx_1e197c9f8d8cdf1" to table: "bugs"
CREATE INDEX "idx_1e197c9f8d8cdf1" ON "public"."bugs" ("engineer_id");
