-- Recreate database
DROP DATABASE quotes;
drop user web_quotes@localhost;
FLUSH PRIVILEGES;
CREATE database quotes;
USE quotes;
-- setup web user
CREATE USER 'web_quotes'@'localhost' IDENTIFIED BY 'thisIsInsecure';
GRANT ALL PRIVILEGES ON quotes.* TO 'web_quotes'@'localhost';
FLUSH PRIVILEGES;
-- Create quotes table
CREATE TABLE quotes(
  id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  author TEXT NOT NULL,
  quote TEXT NOT NULL,
  category TEXT NOT NULL
)ENGINE=MYISAM;
-- Create FULLTEXT index
ALTER TABLE quotes ADD FULLTEXT(author, quote, category);
-- Create searches table
CREATE TABLE searches(
  id INT NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  sessionID VARCHAR(255) NOT NULL,
  searchText VARCHAR(255) NOT NULL,
  amount INT NOT NULL,
  searchTime TIMESTAMP
);
-- Create ratings table
CREATE TABLE ratings(
  id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  sessionID VARCHAR(255) NOT NULL,
  quoteID INT NOT NULL,
  rating FLOAT NOT NULL
);
-- Fill quotes table
LOAD DATA LOCAL INFILE 'quotes_parsed.txt'
    INTO TABLE quotes(quote, author, category);
