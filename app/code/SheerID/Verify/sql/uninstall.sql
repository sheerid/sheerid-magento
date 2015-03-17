DELETE FROM eav_attribute WHERE attribute_code LIKE 'sheerid_%';

ALTER TABLE sales_flat_quote DROP COLUMN sheerid_request_id;
ALTER TABLE sales_flat_quote DROP COLUMN sheerid_result;
ALTER TABLE sales_flat_quote DROP COLUMN sheerid_affiliations;

ALTER TABLE sales_flat_order DROP COLUMN sheerid_request_id;
ALTER TABLE sales_flat_order DROP COLUMN sheerid_result;
ALTER TABLE sales_flat_order DROP COLUMN sheerid_affiliations;
