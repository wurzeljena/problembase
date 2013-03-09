-- -----------------------------------------------------
-- Table `proposers`
-- -----------------------------------------------------
CREATE TABLE proposers (
  id INTEGER NOT NULL,
  name VARCHAR(32) NULL,
  location VARCHAR(32) NULL,
  country VARCHAR(64) NULL,
  email VARCHAR(32) NULL,
  PRIMARY KEY (id ASC) );
CREATE INDEX proposer_name ON proposers(name);

-- -----------------------------------------------------
-- Table `snippets`
-- -----------------------------------------------------
CREATE VIRTUAL TABLE files USING fts4 (
  content TEXT,
  tokenize=simple);

-- -----------------------------------------------------
-- Table `problems`
-- -----------------------------------------------------
CREATE TABLE problems (
  id INTEGER NOT NULL,
  file_id INTEGER NOT NULL,
  proposer_id INTEGER NULL,
  remarks TEXT NULL,
  proposed DATE NULL,
  PRIMARY KEY (id ASC),
  FOREIGN KEY (proposer_id)
    REFERENCES proposers(id)
    ON UPDATE CASCADE);
CREATE INDEX problem_file ON problems(file_id);
CREATE INDEX problem_proposer ON problems(proposer_id);
CREATE INDEX problem_proposed ON problems(proposed);

CREATE TRIGGER delete_problemfile
  BEFORE DELETE ON problems FOR EACH ROW
  BEGIN
    DELETE FROM files WHERE rowid=OLD.file_id;
  END;

-- -----------------------------------------------------
-- Table `solutions`
-- -----------------------------------------------------
CREATE TABLE solutions (
  id INTEGER NOT NULL,
  problem_id INTEGER NOT NULL,
  file_id INTEGER NOT NULL,
  proposer_id INTEGER NULL,
  remarks TEXT NULL,
  year YEAR NULL,
  month TINYINT NULL,
  PRIMARY KEY (id ASC),
  FOREIGN KEY (problem_id)
    REFERENCES problems(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (proposer_id)
    REFERENCES proposers(id)
    ON UPDATE CASCADE);
CREATE INDEX solution_problem ON solutions(problem_id);
CREATE INDEX solution_file ON solutions(file_id);
CREATE INDEX solution_proposer ON solutions(proposer_id);
CREATE INDEX solution_volume ON solutions(year, month);

CREATE TRIGGER delete_solutionfile
  BEFORE DELETE ON solutions FOR EACH ROW
  BEGIN
    DELETE FROM files WHERE rowid=OLD.file_id;
  END;

-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE TABLE users (
  id INTEGER NOT NULL,
  name VARCHAR(32) UNIQUE NOT NULL,
  email VARCHAR(32) UNIQUE NOT NULL,
  encr_pw VARCHAR(64) NULL,
  root TINYINT(1) NOT NULL DEFAULT 0,
  editor TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id ASC) );

-- -----------------------------------------------------
-- Table `comments`
-- -----------------------------------------------------
CREATE TABLE comments (
  user_id INTEGER NOT NULL,
  problem_id INTEGER NOT NULL,
  difficulty TINYINT NULL,
  beauty TINYINT NULL,
  knowledge_required TINYINT NULL,
  comment TEXT NULL,
  PRIMARY KEY (user_id, problem_id),
  FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON UPDATE CASCADE,
  FOREIGN KEY (problem_id)
    REFERENCES problems(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE);
CREATE INDEX comment_user ON comments(user_id);
CREATE INDEX comment_problem ON comments(problem_id);

-- -----------------------------------------------------
-- Table `tags`
-- -----------------------------------------------------
CREATE TABLE tags (
  id INTEGER NOT NULL,
  name VARCHAR(32) UNIQUE NOT NULL,
  description VARCHAR(128) NULL,
  color INT NOT NULL DEFAULT 0,
  hidden BOOL NOT NULL DEFAULT 0,
  PRIMARY KEY (id ASC) );

-- -----------------------------------------------------
-- Table `tag_list`
-- -----------------------------------------------------
CREATE TABLE tag_list (
  problem_id INTEGER NOT NULL,
  tag_id INTEGER NOT NULL,
  PRIMARY KEY (problem_id, tag_id),
  FOREIGN KEY (problem_id)
    REFERENCES problems(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (tag_id)
    REFERENCES tags(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE);

-- -----------------------------------------------------
-- Table `published`
-- -----------------------------------------------------
CREATE TABLE published (
  problem_id INTEGER NOT NULL,
  letter VARCHAR(8) NULL,
  number TINYINT NULL,
  year YEAR NULL,
  month TINYINT NULL,
  PRIMARY KEY (problem_id ASC),
  FOREIGN KEY (problem_id)
    REFERENCES problems(id)
    ON UPDATE CASCADE);
CREATE UNIQUE INDEX published_name ON published(letter, number);
CREATE INDEX published_volume ON published(year, month);
