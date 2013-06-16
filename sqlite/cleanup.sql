--------------------
-- CLEANUP script --
--------------------

-- delete lonely files
DELETE FROM files WHERE rowid NOT IN
  (SELECT file_id FROM problems UNION
  SELECT file_id FROM solutions);

-- delete unused {problem|solution}proposers
DELETE FROM problemproposers WHERE problem_id NOT IN
  (SELECT id FROM problems);
DELETE FROM solutionproposers WHERE solution_id NOT IN
  (SELECT id FROM solutions);

-- delete unused proposer entries
DELETE FROM proposers WHERE id NOT IN
  (SELECT proposer_id FROM problemproposers UNION
  SELECT proposer_id FROM solutionproposers);
