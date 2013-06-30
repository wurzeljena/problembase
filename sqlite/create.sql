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
CREATE UNIQUE INDEX proposer_namelocation ON proposers(name, location);

-- -----------------------------------------------------
-- Table `files`
-- -----------------------------------------------------
CREATE VIRTUAL TABLE files USING fts4 (
  content TEXT,
  tokenize=simple);

-- -----------------------------------------------------
-- Table `problems`
-- -----------------------------------------------------
CREATE TABLE problems (
  file_id INTEGER NOT NULL,
  remarks TEXT NULL,
  proposed DATE NULL,
  public BOOL NOT NULL,
  PRIMARY KEY (file_id ASC) );
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
  file_id INTEGER NOT NULL,
  problem_id INTEGER NOT NULL,
  remarks TEXT NULL,
  year YEAR NULL,
  month TINYINT NULL,
  public BOOL NOT NULL,
  PRIMARY KEY (file_id ASC),
  FOREIGN KEY (problem_id)
    REFERENCES problems(file_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE);
CREATE INDEX solution_problem ON solutions(problem_id);
CREATE INDEX solution_volume ON solutions(year, month);

CREATE TRIGGER delete_solutionfile
  BEFORE DELETE ON solutions FOR EACH ROW
  BEGIN
    DELETE FROM files WHERE rowid=OLD.file_id;
  END;

-- -----------------------------------------------------
-- Table `fileproposers`
-- -----------------------------------------------------
CREATE TABLE fileproposers (
  file_id INTEGER NOT NULL,
  proposer_id INTEGER NOT NULL,
  PRIMARY KEY (file_id, proposer_id),
  FOREIGN KEY (proposer_id)
    REFERENCES proposers(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE);
CREATE INDEX file_proposer ON fileproposers(file_id);

CREATE TRIGGER delete_problemproposers
  BEFORE DELETE ON problems FOR EACH ROW
  BEGIN
    DELETE FROM fileproposers WHERE file_id=OLD.file_id;
  END;
CREATE TRIGGER delete_solutionproposers
  BEFORE DELETE ON solutions FOR EACH ROW
  BEGIN
    DELETE FROM fileproposers WHERE file_id=OLD.file_id;
  END;

-- -----------------------------------------------------
-- Table `pictures`
-- -----------------------------------------------------
CREATE TABLE pictures (
  file_id INTEGER NOT NULL,
  id INTEGER NOT NULL,
  content TEXT NOT NULL,
  public BOOL NOT NULL,
  PRIMARY KEY (file_id ASC, id ASC) );

-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE TABLE users (
  id INTEGER NOT NULL,
  name VARCHAR(32) UNIQUE NOT NULL,
  email VARCHAR(32) UNIQUE NOT NULL,
  encr_pw VARCHAR(120) NULL,
  wait_till DATETIME,
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
  editorial BOOL NOT NULL,
  PRIMARY KEY (user_id, problem_id),
  FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON UPDATE CASCADE,
  FOREIGN KEY (problem_id)
    REFERENCES problems(file_id)
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
-- ... and connection to problems
-- -----------------------------------------------------
CREATE TABLE tag_list (
  problem_id INTEGER NOT NULL,
  tag_id INTEGER NOT NULL,
  PRIMARY KEY (problem_id, tag_id),
  FOREIGN KEY (problem_id)
    REFERENCES problems(file_id)
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
    REFERENCES problems(file_id)
    ON UPDATE CASCADE);
CREATE INDEX published_name ON published(letter, number);
CREATE INDEX published_volume ON published(year, month);
