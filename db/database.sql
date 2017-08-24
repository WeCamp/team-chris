BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS `ratings` (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`place_id`	TEXT NOT NULL,
	`rating`	INTEGER NOT NULL,
	`user_id`	INTEGER DEFAULT 1,
	`category`	TEXT DEFAULT 'lgbt'
);
INSERT INTO `ratings` VALUES (1,'599d8f1f970ae',1,1,'lgbt');
INSERT INTO `ratings` VALUES (2,'599d8f512bc5c',1,1,'lgbt');
INSERT INTO `ratings` VALUES (3,'599d8f522626b',-1,1,'lgbt');
INSERT INTO `ratings` VALUES (4,'599d8f5312a22',-1,1,'lgbt');
INSERT INTO `ratings` VALUES (5,'599d8f5408e4a',-1,1,'lgbt');
INSERT INTO `ratings` VALUES (6,'599d8f522626b',1,1,'vegeterian');
INSERT INTO `ratings` VALUES (7,'599d8f522626b',1,1,'lgbt');
INSERT INTO `ratings` VALUES (8,'599d8f522626b',1,1,'vegan');
COMMIT;
