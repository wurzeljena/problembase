-- -----------------------------------------------------
-- Table `proposers`
-- -----------------------------------------------------
CREATE TABLE proposers (
  id SERIAL NOT NULL,
  name VARCHAR NULL,
  location VARCHAR NULL,
  country VARCHAR NULL,
  email VARCHAR NULL,
  PRIMARY KEY (id) );
CREATE UNIQUE INDEX proposer_namelocation ON proposers(name, location);

-- -----------------------------------------------------
-- Table `files`
-- -----------------------------------------------------
CREATE TABLE files (
  rowid SERIAL NOT NULL,
  content TEXT,
  PRIMARY KEY (rowid));
CREATE INDEX file_content ON files USING gin(to_tsvector('german', content));

CREATE FUNCTION delete_file() RETURNS TRIGGER
  AS $$
  BEGIN
    DELETE FROM files WHERE rowid=OLD.file_id;
    RETURN NEW;
  END
  $$ LANGUAGE plpgsql;

-- -----------------------------------------------------
-- Table `problems`
-- -----------------------------------------------------
CREATE TABLE problems (
  file_id INTEGER NOT NULL,
  remarks TEXT NULL,
  proposed DATE NULL,
  public INTEGER NOT NULL,
  PRIMARY KEY (file_id),
  FOREIGN KEY (file_id)
    REFERENCES files(rowid) );
CREATE UNIQUE INDEX problem_file ON problems(file_id);
CREATE INDEX problem_proposed ON problems(proposed);

CREATE TRIGGER delete_problemfile
  AFTER DELETE ON problems FOR EACH ROW
  EXECUTE PROCEDURE delete_file();

-- -----------------------------------------------------
-- Table `solutions`
-- -----------------------------------------------------
CREATE TABLE solutions (
  file_id INTEGER NOT NULL,
  problem_id INTEGER NOT NULL,
  remarks TEXT NULL,
  year INTEGER NULL,
  month INTEGER NULL,
  public INTEGER NOT NULL,
  PRIMARY KEY (file_id),
  FOREIGN KEY (file_id)
    REFERENCES files(rowid),
  FOREIGN KEY (problem_id)
    REFERENCES problems(file_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE);
CREATE UNIQUE INDEX solution_file ON solutions(file_id);
CREATE INDEX solution_problem ON solutions(problem_id);
CREATE INDEX solution_volume ON solutions(year, month);

CREATE TRIGGER delete_solutionfile
  AFTER DELETE ON solutions FOR EACH ROW
  EXECUTE PROCEDURE delete_file();

-- -----------------------------------------------------
-- Table `fileproposers`
-- -----------------------------------------------------
CREATE TABLE fileproposers (
  file_id INTEGER NOT NULL,
  proposer_id INTEGER NOT NULL,
  PRIMARY KEY (file_id, proposer_id),
  FOREIGN KEY (file_id)
    REFERENCES files(rowid)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (proposer_id)
    REFERENCES proposers(id)
    ON UPDATE CASCADE);
CREATE INDEX fileproposer_file ON fileproposers(file_id);
CREATE INDEX fileproposer_proposer ON fileproposers(proposer_id);

-- -----------------------------------------------------
-- Table `pictures`
-- -----------------------------------------------------
CREATE TABLE pictures (
  file_id INTEGER NOT NULL,
  id INTEGER NOT NULL,
  content TEXT NOT NULL,
  public INTEGER NOT NULL,
  PRIMARY KEY (file_id, id),
  FOREIGN KEY (file_id)
    REFERENCES files(rowid)
    ON DELETE CASCADE
    ON UPDATE CASCADE);
CREATE INDEX picture_file ON pictures(file_id);

-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE TABLE users (
  id SERIAL NOT NULL,
  name VARCHAR UNIQUE NOT NULL,
  email VARCHAR UNIQUE NOT NULL,
  encr_pw VARCHAR NULL,
  wait_till TIME,
  root INTEGER NOT NULL DEFAULT 0,
  editor INTEGER NOT NULL DEFAULT 0,
  PRIMARY KEY (id) );

-- -----------------------------------------------------
-- Table `comments`
-- -----------------------------------------------------
CREATE TABLE comments (
  user_id INTEGER NOT NULL,
  problem_id INTEGER NOT NULL,
  difficulty INTEGER NULL,
  beauty INTEGER NULL,
  knowledge_required INTEGER NULL,
  comment TEXT NULL,
  editorial INTEGER NOT NULL,
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
  id SERIAL NOT NULL,
  name VARCHAR NOT NULL,
  description VARCHAR NULL,
  color INT NOT NULL DEFAULT 0,
  hidden INTEGER NOT NULL DEFAULT 0,
  private_user INTEGER NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (private_user)
    REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE);
CREATE UNIQUE INDEX user_tags ON tags(name, private_user);

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
CREATE INDEX tags_problem ON tag_list(problem_id);
CREATE INDEX tags_tag ON tag_list(tag_id);

-- -----------------------------------------------------
-- Table `published`
-- -----------------------------------------------------
CREATE TABLE published (
  problem_id INTEGER NOT NULL,
  letter VARCHAR NULL,
  number INTEGER NULL,
  year INTEGER NULL,
  month INTEGER NULL,
  PRIMARY KEY (problem_id),
  FOREIGN KEY (problem_id)
    REFERENCES problems(file_id)
    ON UPDATE CASCADE);
CREATE INDEX published_name ON published(letter, number);
CREATE INDEX published_volume ON published(year, month);
