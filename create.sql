SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `problembase` DEFAULT CHARACTER SET latin1 COLLATE latin1_german1_ci ;
USE `problembase` ;

-- -----------------------------------------------------
-- Table `problembase`.`proposers`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `problembase`.`proposers` (
  `id` INT NOT NULL ,
  `name` VARCHAR(32) NULL ,
  `location` VARCHAR(32) NULL ,
  `email` VARCHAR(32) NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `problembase`.`problems`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `problembase`.`problems` (
  `id` INT NOT NULL ,
  `problem` TEXT NULL ,
  `proposer_id` INT NOT NULL ,
  `remarks` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  INDEX `fk_problems_proposers1_idx` (`proposer_id` ASC) ,
  CONSTRAINT `fk_problems_proposers1`
    FOREIGN KEY (`proposer_id` )
    REFERENCES `problembase`.`proposers` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `problembase`.`solutions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `problembase`.`solutions` (
  `id` INT NOT NULL ,
  `problem_id` INT NOT NULL ,
  `solution` TEXT NULL ,
  `proposer_id` INT NOT NULL ,
  PRIMARY KEY (`id`, `problem_id`) ,
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  INDEX `fk_solutions_problems_idx` (`problem_id` ASC) ,
  INDEX `fk_solutions_proposers1_idx` (`proposer_id` ASC) ,
  CONSTRAINT `fk_solutions_problems`
    FOREIGN KEY (`problem_id` )
    REFERENCES `problembase`.`problems` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_solutions_proposers1`
    FOREIGN KEY (`proposer_id` )
    REFERENCES `problembase`.`proposers` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `problembase`.`users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `problembase`.`users` (
  `id` INT NOT NULL ,
  `name` VARCHAR(32) NOT NULL ,
  `email` VARCHAR(32) NULL ,
  `encr_pw` BLOB NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `problembase`.`comments`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `problembase`.`comments` (
  `id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  `problem_id` INT NOT NULL ,
  `difficulty` TINYINT NULL ,
  `beauty` TINYINT NULL ,
  `knowledge_required` TINYINT NULL ,
  `comment` TEXT NULL ,
  PRIMARY KEY (`id`, `user_id`, `problem_id`) ,
  INDEX `fk_comments_users1_idx` (`user_id` ASC) ,
  INDEX `fk_comments_problems1_idx` (`problem_id` ASC) ,
  CONSTRAINT `fk_comments_users1`
    FOREIGN KEY (`user_id` )
    REFERENCES `problembase`.`users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_problems1`
    FOREIGN KEY (`problem_id` )
    REFERENCES `problembase`.`problems` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `problembase`.`tags`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `problembase`.`tags` (
  `id` INT NOT NULL ,
  `name` VARCHAR(32) NOT NULL ,
  `description` VARCHAR(128) NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `problembase`.`tag_list`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `problembase`.`tag_list` (
  `problem_id` INT NOT NULL ,
  `tag_id` INT NOT NULL ,
  PRIMARY KEY (`problem_id`, `tag_id`) ,
  INDEX `fk_tag_list_tags1_idx` (`tag_id` ASC) ,
  CONSTRAINT `fk_tag_list_problems1`
    FOREIGN KEY (`problem_id` )
    REFERENCES `problembase`.`problems` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_tag_list_tags1`
    FOREIGN KEY (`tag_id` )
    REFERENCES `problembase`.`tags` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `problembase`.`published`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `problembase`.`published` (
  `problem_id` INT NOT NULL ,
  `letter` VARCHAR(8) NULL ,
  `number` TINYINT NULL ,
  `month` TINYINT NULL ,
  `year` YEAR NULL ,
  PRIMARY KEY (`problem_id`) ,
  CONSTRAINT `fk_published_problems1`
    FOREIGN KEY (`problem_id` )
    REFERENCES `problembase`.`problems` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
