CREATE TABLE article_translation (id INTEGER, title VARCHAR(255), content VARCHAR(5000), lang CHAR(2), slug VARCHAR(255), PRIMARY KEY(id, lang));
CREATE TABLE article (id INTEGER PRIMARY KEY AUTOINCREMENT);
CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT, article_id INTEGER, author VARCHAR(255), content VARCHAR(5000));
CREATE UNIQUE INDEX article_translation_sluggable_idx ON article_translation (slug, lang, title);
