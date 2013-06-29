--------------------
-- CLEANUP script --
--------------------

-- delete lonely files
DELETE FROM files WHERE rowid NOT IN
  (SELECT file_id FROM problems UNION
  SELECT file_id FROM solutions);

-- delete unused {problem|solution}proposers
DELETE FROM fileproposers WHERE file_id NOT IN
  (SELECT rowid FROM files);

-- delete unused proposer entries
DELETE FROM proposers WHERE id NOT IN
  (SELECT proposer_id FROM fileproposers);
