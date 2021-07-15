CREATE TABLE `common_crawl`.`urls` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `source_url_id` BIGINT NULL,
    `source_domain_id` BIGINT NULL,
    `target_url_id` BIGINT NULL,
    `target_domain_id` BIGINT NULL,
    `url_source` VARCHAR(256) NOT NULL,
    `url_target` VARCHAR(256) NOT NULL,
    `anchor_text` VARCHAR(50) NOT NULL,
    `source_harmonic_centrality` INT NOT NULL,
    `target_harmonic_centrality` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = TokuDB;
