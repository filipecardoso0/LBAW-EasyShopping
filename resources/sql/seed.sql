DROP SCHEMA easyshopping CASCADE;
CREATE SCHEMA easyshopping;
SET search_path TO easyshopping;

-----------------------------------------
-- Types
-----------------------------------------

CREATE TYPE user_type AS ENUM ('Administrator', 'Publisher', 'AuthUser');
CREATE TYPE notification_type AS ENUM ('Wishlist', 'Reviewed');
CREATE TYPE payment_method AS ENUM ('PayPal', 'Visa', 'MasterCard');

-----------------------------------------
-- Tables
-----------------------------------------

CREATE TABLE users (
	userID SERIAL PRIMARY KEY,
	email TEXT UNIQUE NOT NULL,
	username TEXT UNIQUE NOT NULL,
	password TEXT NOT NULL,
	type user_type DEFAULT 'AuthUser' NOT NULL,
	publisher_name TEXT DEFAULT NULL,
	banned BOOLEAN DEFAULT false NOT NULL,
	remember_token TEXT
);


CREATE TABLE category (
        categoryID SERIAL PRIMARY KEY,
        name TEXT NOT NULL
);

CREATE TABLE game (
	gameID SERIAL PRIMARY KEY,
	userID INTEGER DEFAULT NULL REFERENCES users (userID),
	title TEXT NOT NULL,
	description TEXT NOT NULL,
	price NUMERIC(5,2) NOT NULL,
	categoryID INTEGER NOT NULL REFERENCES category (categoryID) ON UPDATE CASCADE,
	release_date TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL,
	classification INTEGER NOT NULL CONSTRAINT classification_ck CHECK (((classification >= 0) AND (classification <= 5))),
	discount NUMERIC(3,2) CONSTRAINT discount_ck CHECK (((discount > 0) AND (discount <= 1))),
	approved BOOLEAN DEFAULT false NOT NULL
);

CREATE TABLE review (
	reviewID SERIAL PRIMARY KEY,
	userID INTEGER NOT NULL REFERENCES users (userID) ON UPDATE CASCADE,
	gameID INTEGER NOT NULL REFERENCES game (gameID) ON UPDATE CASCADE,
	date TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL,
	comment TEXT NOT NULL,
	rating INTEGER NOT NULL CONSTRAINT rating_ck CHECK (((rating > 0) AND (rating <= 5)))
);

CREATE TABLE wishlist (
	userID INTEGER NOT NULL REFERENCES users (userID) ON UPDATE CASCADE,
	gameID INTEGER NOT NULL REFERENCES game (gameID) ON UPDATE CASCADE,
	date TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL,
	PRIMARY KEY (userID, gameID)
);

CREATE TABLE shopping_cart (
	userID INTEGER NOT NULL REFERENCES users (userID) ON UPDATE CASCADE,
	gameID INTEGER NOT NULL REFERENCES game (gameID) ON UPDATE CASCADE,
	game_price NUMERIC(5,2) NOT NULL,
	PRIMARY KEY (userID, gameID)
);

CREATE TABLE order_ (
	userID INTEGER NOT NULL REFERENCES users (userID) ON UPDATE CASCADE,
	gameID INTEGER NOT NULL REFERENCES game (gameID) ON UPDATE CASCADE,
	type payment_method,
	state BOOLEAN DEFAULT NULL,
	value NUMERIC(5,2) NOT NULL,
	order_date TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL,
	PRIMARY KEY (userID, gameID)
);

CREATE TABLE game_order (
	userID INTEGER NOT NULL REFERENCES users (userID) ON UPDATE CASCADE,
	gameID INTEGER NOT NULL REFERENCES game (gameID) ON UPDATE CASCADE,
	price NUMERIC(5,2) NOT NULL,
	PRIMARY KEY (userID, gameID)
);

CREATE TABLE notification(
        notificationID SERIAL PRIMARY KEY,
		userID INTEGER NOT NULL REFERENCES users (userID) ON UPDATE CASCADE,
        read_status BOOLEAN DEFAULT false NOT NULL,
        type notification_type
);

-----------------------------------------
-- INDEXES
-----------------------------------------

CREATE INDEX user_notification ON notification USING hash (userID);

CREATE INDEX user_game ON game USING btree (userID);
CLUSTER game USING user_game;

-- FTS INDEXES
ALTER TABLE game
ADD COLUMN tsvectors TSVECTOR;

-- Create a function to automatically update ts_vectors.
CREATE FUNCTION game_search_update() RETURNS TRIGGER AS $$
BEGIN
 IF TG_OP = 'INSERT' THEN
        NEW.tsvectors = (
         setweight(to_tsvector('english', NEW.title), 'A') ||
         setweight(to_tsvector('english', NEW.categoryID), 'B')
        );
 END IF;
 IF TG_OP = 'UPDATE' THEN
         IF (NEW.title <> OLD.title OR NEW.categoryID <> OLD.categoryID) THEN
           NEW.tsvectors = (
             setweight(to_tsvector('english', NEW.title), 'A') ||
             setweight(to_tsvector('english', NEW.categoryID), 'B')
           );
         END IF;
 END IF;
 RETURN NEW;
END $$
LANGUAGE plpgsql;

-- Create trigger before insert or update on work.
CREATE TRIGGER game_search_update
 BEFORE INSERT OR UPDATE ON game
 FOR EACH ROW
 EXECUTE PROCEDURE game_search_update();

-- Finally, create a GIN index for ts_vectors.
CREATE INDEX search_idx ON game USING GIN (tsvectors);

-----------------------------------------
-- TRIGGERS and UDFs
-----------------------------------------

-- Sends a notification to the user that is the gamepublisher of the game reviewed
CREATE OR REPLACE FUNCTION notifyReview()
RETURNS TRIGGER AS
$$
DECLARE
	userIdentifier int;
BEGIN
	userIdentifier = (Select userID from game Where NEW.gameID=gameID);
	INSERT INTO notification VALUES (DEFAULT, userIdentifier, false, 'Reviewed');
	RETURN NEW;
END;
$$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS notify_review ON review;
CREATE TRIGGER notify_review
AFTER INSERT ON review
FOR EACH ROW
EXECUTE PROCEDURE notifyReview();

-- Sends a notification to the user that the game on his wishlist is now on sale
CREATE OR REPLACE FUNCTION notifyWishlist()
RETURNS TRIGGER AS
$$
BEGIN
    IF EXISTS (Select gameID from game WHERE OLD.discount < NEW.discount AND OLD.gameID = NEW.gameID) THEN
		INSERT INTO notification (userid, read_status, type)
		(SELECT userid, 'false', 'Wishlist' FROM wishlist WHERE gameid = NEW.gameID);
	END IF;
    RETURN NEW;
END;
$$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS notify_wishlist ON game;
CREATE TRIGGER notify_wishlist
BEFORE UPDATE ON game
FOR EACH ROW
EXECUTE PROCEDURE notifyWishlist();


-- It is not possible to review a game that hasn't been launched yet
CREATE OR REPLACE FUNCTION reviewnotLaunched()
RETURNS TRIGGER AS
$$
DECLARE
    gamedate TIMESTAMP WITH TIME ZONE;
BEGIN
    gamedate = (SELECT release_date FROM game WHERE NEW.gameID = gameID);
    IF EXISTS (SELECT * FROM review WHERE NEW.date < gamedate) THEN
        RAISE EXCEPTION 'A game can only be reviewed after it has been launched.';
    END IF;
    RETURN NEW;
END;
$$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS review_notlaunched ON review;
CREATE TRIGGER review_notlaunched
BEFORE INSERT ON review
FOR EACH ROW
EXECUTE PROCEDURE reviewnotLaunched();

-- A publisher cannot review his published games
CREATE OR REPLACE FUNCTION reviewPublishedGame()
RETURNS TRIGGER AS
$$
DECLARE
BEGIN
    IF EXISTS (SELECT * FROM game WHERE NEW.gameID = gameID AND NEW.userID = userID) THEN
        RAISE EXCEPTION 'A game cannot be reviewed by its publisher.';
    END IF;
    RETURN NEW;
END;
$$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS review_publishedgame ON review;
CREATE TRIGGER review_publishedgame
BEFORE INSERT ON review
FOR EACH ROW
EXECUTE PROCEDURE reviewPublishedGame();


-- It is not possible to review a game that the user hasn't been bought yet
CREATE OR REPLACE FUNCTION reviewNotBought()
RETURNS TRIGGER AS
$$
DECLARE
    orderstate BOOLEAN;
BEGIN
    orderstate = (SELECT state FROM order_ WHERE NEW.gameID = gameID AND NEW.userID = userID);
    IF orderstate = true THEN
  		RETURN NEW;
    ELSE
		RAISE EXCEPTION 'A game can only be reviewed after it has been purchased.';
	END IF;
    RETURN NEW;
END;
$$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS review_notbought ON review;
CREATE TRIGGER review_notbought
BEFORE INSERT ON review
FOR EACH ROW
EXECUTE PROCEDURE reviewNotBought();


-- When a new review on a certain game is posted the game classification is updated
CREATE OR REPLACE FUNCTION updateClassification()
RETURNS TRIGGER AS
$$
DECLARE
    newavgreview int;
BEGIN
    newavgreview = (Select AVG(rating) FROM review Where NEW.gameID = gameID);
    UPDATE game SET classification = newavgreview Where NEW.gameID = gameID;
    RETURN NEW;
END;
$$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS update_classification ON review;
CREATE TRIGGER update_classification
AFTER INSERT ON review
FOR EACH ROW
EXECUTE PROCEDURE updateClassification();

-- When a review on a certain game is deleted the game classification is updated
CREATE OR REPLACE FUNCTION updateRemovedClassification()
RETURNS TRIGGER AS
$$
DECLARE
    newavgreview int;
BEGIN
    newavgreview = (Select AVG(rating) FROM review Where OLD.gameID = gameID);
    UPDATE game SET classification = newavgreview Where OLD.gameID = gameID;
    RETURN NEW;
END;
$$
LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS update_removedclassification ON review;
CREATE TRIGGER update_removedclassification
AFTER DELETE ON review
FOR EACH ROW
EXECUTE PROCEDURE updateRemovedClassification();

-----------------------------------------
-- Transactions
-----------------------------------------

--Adds a product and its info to a given user shopping cart
CREATE OR REPLACE FUNCTION addProductToShoppingCart(userIdentifier int, gameIdentifier int)
returns void
as
$$
DECLARE
	gameprice int;
BEGIN
	gameprice = (Select price FROM game Where gameId = gameIdentifier);
	INSERT INTO shopping_cart VALUES(userIdentifier, gameIdentifier, gameprice);
END;
$$
language plpgsql;

--Adds user games on shopping_cart and its prices to the table responsible for the checkout
CREATE OR REPLACE FUNCTION transGameToCheckout(userIdentifier int)
returns void
as
$$
BEGIN
	INSERT INTO game_order (userid, gameID, price)
	(SELECT userid, gameID, game_price FROM shopping_cart WHERE userID = userIdentifier);
	-- Deletes games from shopping cart
	DELETE FROM shopping_cart WHERE userID = userIdentifier;
END;
$$
language plpgsql;

