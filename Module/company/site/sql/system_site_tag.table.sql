CREATE TABLE system_site_tag(
    site_id VARCHAR(50),
    tag VARCHAR(50),
    PRIMARY KEY(site_id, tag)
);

CREATE INDEX idx_sitetag ON system_site_tag(tag);
