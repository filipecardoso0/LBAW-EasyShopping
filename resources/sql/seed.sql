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
    id SERIAL NOT NULL,
	userID INTEGER NOT NULL REFERENCES users (userID) ON UPDATE CASCADE,
	gameID INTEGER NOT NULL REFERENCES game (gameID) ON UPDATE CASCADE,
	game_price NUMERIC(5,2) NOT NULL,
	PRIMARY KEY (userID, gameID)
);

CREATE TABLE order_ (
    orderID SERIAL NOT NULL,
    userID INTEGER NOT NULL REFERENCES users (userID) ON UPDATE CASCADE,
    type payment_method,
    state BOOLEAN DEFAULT NULL,
    value NUMERIC(5,2) NOT NULL,
    order_date TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL,
    PRIMARY KEY (orderID)
);

CREATE TABLE game_order (
    id SERIAL NOT NULL,
    orderID INTEGER NOT NULL REFERENCES order_ (orderID) ON UPDATE CASCADE,
    gameID INTEGER NOT NULL REFERENCES game (gameID) ON UPDATE CASCADE,
    price NUMERIC(5,2) NOT NULL,
    PRIMARY KEY (orderID, gameID)
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

-- FTS

-- Add column to work to store computed ts_vectors.
ALTER TABLE game ADD COLUMN tsvectors TSVECTOR;

-- Create a function to automatically update ts_vectors.
CREATE FUNCTION game_search_update() RETURNS TRIGGER AS $BODY$
BEGIN
    IF TG_OP = 'INSERT' THEN
        NEW.tsvectors = setweight(to_tsvector('english', new.title), 'A') ||
                        setweight(to_tsvector('english', new.description), 'B');
    END IF;
    IF TG_OP = 'UPDATE' THEN
         IF (NEW.title <> OLD.title) OR (NEW.description <> OLD.description) THEN
           NEW.tsvectors = setweight(to_tsvector('english', new.title), 'A') ||
                           setweight(to_tsvector('english', new.description), 'B');
    END IF;
 END IF;
 RETURN NEW;
END $BODY$
LANGUAGE plpgsql;

-- Create a trigger before insert or update on work.
CREATE TRIGGER game_search_update
 BEFORE INSERT OR UPDATE ON game
 FOR EACH ROW
 EXECUTE PROCEDURE game_search_update();

-- Finally, create a GIN index for ts_vectors.
CREATE INDEX game_search_idx ON game USING GIN (tsvectors);

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
--TODO FIX THIS FUNCTION
/*
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
*/

-----------------------------------------
-- Populate
-----------------------------------------

-- Populate Users Table
insert into users (email, username, password, type, publisher_name, banned) values ('user@example.com', 'user', '$2a$04$qmD/OKqa2xeSIRVz1URFXuw8Us9jz7UP4f3EugSFFRFjHGmdHnX5a', 'AuthUser', 'Crown Castle International Corporation', false);
insert into users (email, username, password, type, publisher_name, banned) values ('iguildford0@domainmarket.com', 'hcurley0', 'BIwCug7Ev', 'Publisher', 'Crown Castle International Corporation', false);
insert into users (email, username, password, type, publisher_name, banned) values ('dkinneally1@xrea.com', 'kbrecher1', 'eqkJTnUGf', 'AuthUser', 'support.com, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('areeders2@pcworld.com', 'achingedehals2', 'KY6jK4y', 'AuthUser', 'Nam Tai Property Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('jfrayn3@dedecms.com', 'cenglish3', '6vT7T4', 'AuthUser', 'Sturm, Ruger & Company, Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('dphilbrick4@si.edu', 'vblant4', 'jlnn5UC', 'Administrator', 'Central Garden & Pet Company', true);
insert into users (email, username, password, type, publisher_name, banned) values ('tmorsley5@loc.gov', 'ngeare5', '0DlhKzmj', 'AuthUser', 'Rambus, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('poduilleain6@histats.com', 'kbreach6', 'XkL6OTyOMZ', 'Publisher', 'Cadiz, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('iceely7@mashable.com', 'mashbolt7', 'PCuWi2kG2VwB', 'AuthUser', 'Lion Biotechnologies, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('mguillford8@list-manage.com', 'epeever8', 'JwEk4VaP5riI', 'Publisher', 'Stratus Properties Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('yranson9@studiopress.com', 'striggol9', 'i6ER5GQa', 'Publisher', 'Colony NorthStar, Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('biacovaccia@accuweather.com', 'lfosbraeya', 'Ze3536UkukE', 'AuthUser', 'Champions Oncology, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('eblacklockb@sina.com.cn', 'pfairbrotherb', 'C18cn9mX7bRp', 'AuthUser', 'Forward Pharma A/S', false);
insert into users (email, username, password, type, publisher_name, banned) values ('ogrellierc@msn.com', 'sheinsenc', 'uSDGhMBVF', 'AuthUser', 'Netflix, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('gpitcherd@upenn.edu', 'rmossond', 'J8EyTtLF8M', 'Publisher', 'Gorman-Rupp Company (The)', true);
insert into users (email, username, password, type, publisher_name, banned) values ('dkistinge@tamu.edu', 'gmerrigane', 'lMlSEh', 'AuthUser', 'Zumiez Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('rdungatef@guardian.co.uk', 'kaxtonnef', 'gZapWrxO', 'Publisher', 'Wintrust Financial Corporation', true);
insert into users (email, username, password, type, publisher_name, banned) values ('jslateng@vkontakte.ru', 'gcamierg', 'EseDNd8', 'Administrator', 'Universal Health Services, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('oimbreyh@springer.com', 'lonearyh', 'UH53xQx', 'Administrator', 'Stage Stores, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('cdouthwaitei@rambler.ru', 'lmissingtoni', 'LSAJQGV9', 'Administrator', 'Endurance Specialty Holdings Ltd', true);
insert into users (email, username, password, type, publisher_name, banned) values ('tgogginj@amazonaws.com', 'tivashinj', 'zce2EDqe', 'AuthUser', 'RSP Permian, Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('etolandk@ucoz.com', 'awhiterodk', 'tC9qM0jKj', 'Administrator', 'Uni-Pixel, Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('athackerayl@acquirethisname.com', 'rfollinl', 'PJalR8mgzpK9', 'Administrator', 'TPI Composites, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('mpaurm@spotify.com', 'fircem', 'CuSv8S', 'Administrator', 'Banco Santander, S.A.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('gpetherickn@nba.com', 'edehoochn', 'bANlzQKmEGcI', 'Administrator', 'Hovnanian Enterprises Inc', true);
insert into users (email, username, password, type, publisher_name, banned) values ('rsaleo@ask.com', 'fwitherbedo', '7OAIGPb9uA', 'Administrator', 'Travelport Worldwide Limited', false);
insert into users (email, username, password, type, publisher_name, banned) values ('mrankcomp@naver.com', 'fcopnallp', 'UYNgPeL', 'AuthUser', 'Blackrock MuniVest Fund II, Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('einchanq@house.gov', 'ihintzeq', 'KIh2xjkMqXnc', 'AuthUser', 'First Trust/Aberdeen Emerging Opportunity Fund', false);
insert into users (email, username, password, type, publisher_name, banned) values ('tblackdenr@csmonitor.com', 'bbettyr', '65jEj8aLTbK', 'Publisher', 'GP Investments Acquisition Corp.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('rmellors@mediafire.com', 'cmcgaherns', 'uhtgsRvHgiMH', 'Administrator', 'Gol Linhas Aereas Inteligentes S.A.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('jrollingt@clickbank.net', 'fklousnert', 'xrLFYvRMQ', 'Publisher', 'Multi-Color Corporation', true);
insert into users (email, username, password, type, publisher_name, banned) values ('jdogeu@zdnet.com', 'vkippinsu', 'iAjEYf1HC', 'AuthUser', 'American Airlines Group, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('hosbaldstonev@bandcamp.com', 'hgiottoiv', '1gkds4', 'AuthUser', 'Costamare Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('nbedissw@oaic.gov.au', 'jcamellw', 'nRmWBxmAS', 'AuthUser', 'First Bancorp, Inc (ME)', false);
insert into users (email, username, password, type, publisher_name, banned) values ('nguisox@homestead.com', 'cpearex', 'xfEw34Vf', 'AuthUser', 'HSN, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('gtuckeyy@istockphoto.com', 'rhemerijky', 'zv0xVpW', 'Administrator', 'Johnson Controls International plc', false);
insert into users (email, username, password, type, publisher_name, banned) values ('tyuz@dagondesign.com', 'ireddz', 'SabJPGNiWK', 'Administrator', 'Ellie Mae, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('tmunn10@smh.com.au', 'imcaster10', 'A9cTzs', 'Administrator', 'Oi S.A.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('dhovenden11@bbc.co.uk', 'mbabst11', 'YeAzV2cPn', 'AuthUser', 'CoreLogic, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('hserfati12@rakuten.co.jp', 'nwitt12', '1cWPpvnp', 'Publisher', 'J P Morgan Chase & Co', true);
insert into users (email, username, password, type, publisher_name, banned) values ('atomasini13@mediafire.com', 'nfiddyment13', 'd0SZ6rQhl6', 'Administrator', 'Southern Missouri Bancorp, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('dclausson14@webmd.com', 'ewhenham14', '60mVSwrwoASX', 'AuthUser', 'First US Bancshares, Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('mricardin15@hubpages.com', 'ffarrears15', 'Sz4eM5aa', 'Administrator', 'Tilly''s, Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('adeangelo16@blogs.com', 'kantcliff16', '3jheAj', 'AuthUser', 'GNC Holdings, Inc.', false);
insert into users (email, username, password, type, publisher_name, banned) values ('klinneman17@free.fr', 'rkneeshaw17', 'GqHU06H', 'Publisher', 'Fresh Del Monte Produce, Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('egossipin18@sitemeter.com', 'aantonomolii18', 'CMaQ0gRT', 'AuthUser', 'PIMCO Municipal Income Fund III', true);
insert into users (email, username, password, type, publisher_name, banned) values ('bprickett19@addtoany.com', 'mmonte19', 'MAxKZ6s', 'Publisher', 'Agenus Inc.', true);
insert into users (email, username, password, type, publisher_name, banned) values ('vkillock1a@miitbeian.gov.cn', 'bwharram1a', 'oiTd7v', 'Administrator', 'Hexcel Corporation', true);
insert into users (email, username, password, type, publisher_name, banned) values ('fdresse1b@deviantart.com', 'ahintzer1b', 'FfKLTMk', 'Publisher', 'LaSalle Hotel Properties', false);
insert into users (email, username, password, type, publisher_name, banned) values ('hblampy1c@hostgator.com', 'pjosiah1c', 'wLQ9nfvle', 'Publisher', 'Gabelli Equity Trust, Inc. (The)', true);
insert into users (email, username, password, type, publisher_name, banned) values ('jtille1d@cnet.com', 'jambrosetti1d', '7ykIBtOfvE', 'Publisher', 'Ceragon Networks Ltd.', true);

-- Populate Category Table
insert into category (name) values ('Sandbox');
insert into category (name) values ('Real-time strategy (RTS)');
insert into category (name) values ('Shooters (FPS and TPS)');
insert into category (name) values ('Multiplayer online battle arena (MOBA)');
insert into category (name) values ('Role-playing (RPG, ARPG, and More)');
insert into category (name) values ('Simulation and sports');
insert into category (name) values ('Puzzlers and party games');
insert into category (name) values ('Action-adventure');
insert into category (name) values ('Survival and horror');
insert into category (name) values ('Platformer');

-- Populate Game Table
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (18, 'Bye Bye Monkey (Ciao maschio)', 'Progressive systematic synergy', 49, 8, '2011-04-22', 2, 0.05, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (3, 'The Harmony Game', 'Multi-layered national algorithm', 40, 2, '2011-03-28', 3, 0.39, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (13, 'Helter Skelter', 'De-engineered methodical core', 81, 1, '2021-05-09', 1, 0.29, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (37, 'Peaceful Warrior', 'Cross-platform modular focus group', 58, 1, '2018-01-25', 2, 0.13, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (32, 'Pit, The', 'Business-focused human-resource product', 31, 6, '2016-05-30', 5, 0.18, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (4, 'Alphabet City', 'Ergonomic 5th generation moratorium', 63, 7, '2017-12-13', 1, 0.01, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (35, 'Blankman', 'Right-sized next generation focus group', 38, 9, '2020-06-10', 1, 0.58, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (4, 'Moonlight and Cactus', 'Focused maximized encryption', 89, 6, '2012-12-23', 5, 0.96, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (42, 'That Was Then... This Is Now', 'Focused tertiary workforce', 22, 1, '2012-04-04', 3, 0.34, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (14, 'Sorority House Massacre II', 'Cloned full-range workforce', 19, 6, '2017-10-24', 1, 0.77, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (24, 'Believer, The', 'Proactive well-modulated interface', 81, 10, '2009-05-26', 3, 0.57, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (44, 'Rain Over Santiago (Il pleut sur Santiago)', 'Public-key optimizing interface', 68, 6, '2012-09-27', 4, 0.26, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (10, 'Russell Peters: Red, White and Brown', 'Advanced analyzing artificial intelligence', 7, 6, '2009-09-15', 5, 0.58, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (18, 'Narcos', 'Ameliorated dedicated strategy', 57, 1, '2013-08-26', 3, 0.71, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (9, 'Feds', 'Total global encoding', 22, 7, '2021-09-12', 4, 0.04, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (10, 'Man - Woman Wanted (Poszukiwany, poszukiwana)', 'Programmable stable project', 95, 4, '2022-01-07', 3, 0.31, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (19, 'Alice in Wonderland', 'Organized even-keeled system engine', 74, 5, '2012-05-07', 2, 0.89, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (43, 'Carry on Cruising', 'Future-proofed homogeneous support', 34, 5, '2015-08-11', 4, 0.19, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (50, 'Kevin Hart: Let Me Explain', 'Digitized 4th generation budgetary management', 79, 5, '2018-01-16', 4, 0.53, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (25, 'Karlsson Brothers (Bröderna Karlsson)', 'Re-engineered leading edge capability', 27, 1, '2017-03-08', 3, 0.86, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (34, 'No Reservations', 'De-engineered next generation neural-net', 91, 4, '2018-12-04', 2, 0.04, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (49, 'Stealing a Nation', 'Quality-focused intangible budgetary management', 1, 6, '2008-05-26', 1, 0.37, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (39, 'I Went Down', 'Cross-group needs-based strategy', 94, 6, '2008-04-05', 2, 0.24, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (38, 'Tall Guy, The', 'Enterprise-wide background analyzer', 13, 4, '2010-01-27', 3, 0.37, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (18, 'Words, The', 'Digitized impactful access', 5, 9, '2017-06-05', 1, 0.28, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (33, 'Pendulum', 'Object-based upward-trending benchmark', 40, 7, '2016-05-19', 1, 0.94, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (42, 'Total Recall', 'Cross-group explicit frame', 5, 4, '2012-01-15', 1, 0.6, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (2, 'Three Faces of Eve, The', 'Multi-layered bifurcated matrix', 57, 3, '2022-01-03', 4, 0.54, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (41, 'Just Pals', 'Managed intangible project', 33, 4, '2018-03-04', 1, 0.33, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (21, 'Details, The', 'User-centric cohesive hierarchy', 8, 2, '2019-03-25', 3, 0.68, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (19, 'Midnight Dancers (Sibak)', 'Multi-channelled context-sensitive analyzer', 51, 4, '2015-05-23', 3, 0.96, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (37, 'Hitler''s Children', 'Mandatory incremental infrastructure', 56, 5, '2011-09-17', 4, 0.27, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (20, 'Prison Break: The Final Break', 'Organized motivating product', 81, 7, '2022-10-19', 3, 0.26, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (46, 'Kimberly', 'Adaptive real-time access', 13, 2, '2012-02-20', 4, 0.24, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (50, 'American Hustle', 'Diverse dedicated approach', 83, 5, '2010-10-31', 2, 0.41, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (45, 'Cargo', 'Re-contextualized stable ability', 45, 6, '2021-10-07', 2, 0.72, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (40, 'Zero Kelvin (Kjærlighetens kjøtere)', 'Intuitive contextually-based benchmark', 27, 3, '2021-01-10', 1, 0.59, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (10, 'Timecop', 'Realigned hybrid synergy', 87, 9, '2012-06-16', 4, 0.6, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (40, 'Haunting in Connecticut, The', 'Customizable leading edge task-force', 58, 2, '2009-10-10', 5, 0.58, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (13, 'Warrior and the Sorceress, The', 'Phased homogeneous time-frame', 67, 10, '2017-02-22', 3, 0.98, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (42, 'Beast, The (La bête)', 'Focused asymmetric utilisation', 29, 9, '2008-09-10', 5, 0.28, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (46, 'Those Who Love Me Can Take the Train (Ceux qui m''aiment prendront le train)', 'Fundamental radical leverage', 36, 1, '2011-05-24', 4, 0.68, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (14, 'End of America, The', 'Pre-emptive solution-oriented architecture', 59, 9, '2010-04-18', 2, 0.67, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (10, 'Mona Lisa Smile', 'Multi-layered object-oriented capability', 24, 4, '2018-08-05', 1, 0.17, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (9, 'Knight Moves', 'Triple-buffered background adapter', 86, 4, '2009-05-31', 1, 0.55, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (3, 'Valhalla Rising', 'Vision-oriented modular moderator', 91, 2, '2009-01-06', 2, 0.79, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (14, 'Ritual (Shiki-Jitsu)', 'Innovative motivating encryption', 38, 2, '2018-02-06', 1, 0.53, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (17, 'Girl from Monday, The', 'Self-enabling mission-critical system engine', 21, 10, '2013-08-16', 3, 0.89, true);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (13, 'Shakiest Gun in the West, The', 'Proactive upward-trending initiative', 91, 10, '2010-05-29', 4, 0.94, false);
insert into game (userID, title, description, price, categoryID, release_date, classification, discount, approved) values (37, 'City Zero', 'Visionary tertiary algorithm', 52, 8, '2012-06-07', 5, 0.01, false);

-- Populate Table Review
/* Take a look at it later
insert into review (userID, gameID, date, comment, rating) values (12, 29, '2012-10-16', 'Its OK', 3.16);
insert into review (userID, gameID, date, comment, rating) values (16, 33, '2008-02-14', 'Worth the shot', 2.98);
insert into review (userID, gameID, date, comment, rating) values (19, 27, '2020-05-02', 'Not worth its price', 3.17);
insert into review (userID, gameID, date, comment, rating) values (42, 5, '2018-02-28', 'Best game out there. However servers are so so', 4.67);
insert into review (userID, gameID, date, comment, rating) values (27, 35, '2006-09-03', 'What is this?', 2.49);
insert into review (userID, gameID, date, comment, rating) values (1, 45, '2012-08-06', 'Loved it, but gameplay mechanics needs some fixing', 3.82);
insert into review (userID, gameID, date, comment, rating) values (17, 46, '2008-08-13', 'Nice game, however needs some optimization', 4.42);
insert into review (userID, gameID, date, comment, rating) values (18, 10, '2017-12-18', 'Bad servers, bad graphics, bad performance. To sum up, do not install', 1.31);
insert into review (userID, gameID, date, comment, rating) values (8, 5, '2011-09-09', 'This game has a bright future ahead', 3.41);
insert into review (userID, gameID, date, comment, rating) values (41, 14, '2007-10-29', '<3', 4.29);
insert into review (userID, gameID, date, comment, rating) values (13, 41, '2018-04-02', 'Cheaters have ruined this game', 1.13);
insert into review (userID, gameID, date, comment, rating) values (34, 7, '2022-02-28', 'Do your self a favor and buy it! You will not regret it', 4.77);
insert into review (userID, gameID, date, comment, rating) values (9, 33, '2022-09-12', 'Even 90s games are better than this', 2.11);
insert into review (userID, gameID, date, comment, rating) values (21, 7, '2012-04-05', 'Promising game with bad developers', 3.11);
insert into review (userID, gameID, date, comment, rating) values (39, 19, '2011-07-22', 'Are the devs OK?', 1.42);
insert into review (userID, gameID, date, comment, rating) values (33, 35, '2017-07-24', 'Game of the year! I play it every single day', 4.93);
insert into review (userID, gameID, date, comment, rating) values (17, 50, '2022-03-28', 'Awful', 1.17);
insert into review (userID, gameID, date, comment, rating) values (7, 28, '2012-03-12', 'Please do yourself a favor and do not buy this game', 1.08);
insert into review (userID, gameID, date, comment, rating) values (45, 11, '2014-08-20', 'Not worth the price', 2.29);
insert into review (userID, gameID, date, comment, rating) values (24, 22, '2007-07-08', 'Loved it! Graphics are phenomenal.', 3.91);
insert into review (userID, gameID, date, comment, rating) values (26, 27, '2022-08-13', 'I regret so much buying it', 1.83);
insert into review (userID, gameID, date, comment, rating) values (9, 4, '2021-02-13', 'Love the game, but the servers need some improvements', 3.16);
insert into review (userID, gameID, date, comment, rating) values (15, 14, '2012-03-17', 'Story mode was completely awful', 1.98);
insert into review (userID, gameID, date, comment, rating) values (6, 12, '2008-04-08', 'Needs some improvements, but overall has a really nice gameplay', 3.14);
insert into review (userID, gameID, date, comment, rating) values (19, 26, '2011-03-03', 'Give it a try, I guess', 3.46);
insert into review (userID, gameID, date, comment, rating) values (7, 14, '2019-07-13', 'Hated it', 1.49);
insert into review (userID, gameID, date, comment, rating) values (7, 38, '2007-02-23', 'Cool gameplay, but poor graphics', 3.72);
insert into review (userID, gameID, date, comment, rating) values (33, 1, '2013-09-20', 'Do not waste your time on this', 2.0);
insert into review (userID, gameID, date, comment, rating) values (19, 34, '2022-11-03', 'Please Fix it', 2.36);
insert into review (userID, gameID, date, comment, rating) values (24, 38, '2010-09-19', 'Poor gameplay mechanics', 1.6);
insert into review (userID, gameID, date, comment, rating) values (10, 24, '2021-11-28', 'Really nice graphics', 4.08);
 */

-- Populate Table Wishlist
insert into wishlist (userID, gameID, date) values (1, 35, '2013-04-06');
insert into wishlist (userID, gameID, date) values (2, 46, '2017-05-11');
insert into wishlist (userID, gameID, date) values (3, 9, '2015-11-04');
insert into wishlist (userID, gameID, date) values (4, 29, '2019-11-25');
insert into wishlist (userID, gameID, date) values (5, 24, '2014-05-31');
insert into wishlist (userID, gameID, date) values (6, 19, '2011-08-30');
insert into wishlist (userID, gameID, date) values (7, 48, '2017-12-08');
insert into wishlist (userID, gameID, date) values (8, 17, '2014-12-23');
insert into wishlist (userID, gameID, date) values (9, 8, '2007-06-12');
insert into wishlist (userID, gameID, date) values (10, 37, '2021-03-18');
insert into wishlist (userID, gameID, date) values (11, 1, '2009-08-05');
insert into wishlist (userID, gameID, date) values (12, 28, '2009-01-26');
insert into wishlist (userID, gameID, date) values (13, 46, '2008-11-19');
insert into wishlist (userID, gameID, date) values (14, 22, '2016-11-26');
insert into wishlist (userID, gameID, date) values (15, 8, '2018-07-21');
insert into wishlist (userID, gameID, date) values (16, 25, '2016-01-09');
insert into wishlist (userID, gameID, date) values (17, 47, '2022-03-24');
insert into wishlist (userID, gameID, date) values (18, 2, '2022-11-12');
insert into wishlist (userID, gameID, date) values (19, 21, '2018-10-22');
insert into wishlist (userID, gameID, date) values (20, 48, '2019-04-14');
insert into wishlist (userID, gameID, date) values (21, 9, '2019-11-08');
insert into wishlist (userID, gameID, date) values (22, 39, '2009-07-13');
insert into wishlist (userID, gameID, date) values (23, 37, '2015-05-21');
insert into wishlist (userID, gameID, date) values (24, 42, '2013-12-07');
insert into wishlist (userID, gameID, date) values (25, 34, '2015-01-24');
insert into wishlist (userID, gameID, date) values (26, 49, '2012-01-06');
insert into wishlist (userID, gameID, date) values (27, 50, '2012-04-17');
insert into wishlist (userID, gameID, date) values (28, 25, '2011-08-23');
insert into wishlist (userID, gameID, date) values (29, 44, '2015-10-01');
insert into wishlist (userID, gameID, date) values (30, 38, '2014-10-08');
insert into wishlist (userID, gameID, date) values (31, 26, '2019-11-14');
insert into wishlist (userID, gameID, date) values (32, 45, '2016-07-14');
insert into wishlist (userID, gameID, date) values (33, 1, '2021-06-07');
insert into wishlist (userID, gameID, date) values (34, 34, '2022-04-04');
insert into wishlist (userID, gameID, date) values (35, 36, '2014-12-05');
insert into wishlist (userID, gameID, date) values (36, 28, '2021-07-21');
insert into wishlist (userID, gameID, date) values (37, 27, '2017-03-22');
insert into wishlist (userID, gameID, date) values (38, 26, '2011-04-09');
insert into wishlist (userID, gameID, date) values (39, 10, '2010-06-19');
insert into wishlist (userID, gameID, date) values (40, 23, '2016-03-13');
insert into wishlist (userID, gameID, date) values (41, 36, '2023-01-09');
insert into wishlist (userID, gameID, date) values (42, 49, '2012-08-11');
insert into wishlist (userID, gameID, date) values (43, 6, '2014-06-14');
insert into wishlist (userID, gameID, date) values (44, 17, '2021-12-26');
insert into wishlist (userID, gameID, date) values (45, 48, '2007-12-13');
insert into wishlist (userID, gameID, date) values (46, 44, '2009-10-16');
insert into wishlist (userID, gameID, date) values (47, 37, '2014-10-25');
insert into wishlist (userID, gameID, date) values (48, 5, '2015-01-11');
insert into wishlist (userID, gameID, date) values (49, 14, '2012-03-08');
insert into wishlist (userID, gameID, date) values (50, 31, '2007-07-03');

-- Populate Table Shopping Cart
insert into shopping_cart (userID, gameID, game_price) values (1, 22, 145);
insert into shopping_cart (userID, gameID, game_price) values (2, 7, 58);
insert into shopping_cart (userID, gameID, game_price) values (3, 4, 9);
insert into shopping_cart (userID, gameID, game_price) values (4, 2, 109);
insert into shopping_cart (userID, gameID, game_price) values (5, 31, 64);
insert into shopping_cart (userID, gameID, game_price) values (6, 23, 142);
insert into shopping_cart (userID, gameID, game_price) values (7, 38, 40);
insert into shopping_cart (userID, gameID, game_price) values (8, 24, 43);
insert into shopping_cart (userID, gameID, game_price) values (9, 7, 38);
insert into shopping_cart (userID, gameID, game_price) values (10, 31, 156);
insert into shopping_cart (userID, gameID, game_price) values (11, 10, 174);
insert into shopping_cart (userID, gameID, game_price) values (12, 43, 46);
insert into shopping_cart (userID, gameID, game_price) values (13, 12, 172);
insert into shopping_cart (userID, gameID, game_price) values (14, 5, 18);
insert into shopping_cart (userID, gameID, game_price) values (15, 25, 167);
insert into shopping_cart (userID, gameID, game_price) values (16, 39, 137);
insert into shopping_cart (userID, gameID, game_price) values (17, 27, 89);
insert into shopping_cart (userID, gameID, game_price) values (18, 12, 113);
insert into shopping_cart (userID, gameID, game_price) values (19, 12, 154);
insert into shopping_cart (userID, gameID, game_price) values (20, 40, 148);
insert into shopping_cart (userID, gameID, game_price) values (21, 43, 139);
insert into shopping_cart (userID, gameID, game_price) values (22, 31, 26);
insert into shopping_cart (userID, gameID, game_price) values (23, 31, 181);
insert into shopping_cart (userID, gameID, game_price) values (24, 15, 63);
insert into shopping_cart (userID, gameID, game_price) values (25, 5, 2);
insert into shopping_cart (userID, gameID, game_price) values (26, 4, 160);
insert into shopping_cart (userID, gameID, game_price) values (27, 12, 135);
insert into shopping_cart (userID, gameID, game_price) values (28, 45, 7);
insert into shopping_cart (userID, gameID, game_price) values (29, 3, 50);
insert into shopping_cart (userID, gameID, game_price) values (30, 3, 128);
insert into shopping_cart (userID, gameID, game_price) values (31, 23, 148);
insert into shopping_cart (userID, gameID, game_price) values (32, 8, 193);
insert into shopping_cart (userID, gameID, game_price) values (33, 27, 133);
insert into shopping_cart (userID, gameID, game_price) values (34, 46, 97);
insert into shopping_cart (userID, gameID, game_price) values (35, 42, 162);
insert into shopping_cart (userID, gameID, game_price) values (36, 42, 176);
insert into shopping_cart (userID, gameID, game_price) values (37, 35, 159);
insert into shopping_cart (userID, gameID, game_price) values (38, 43, 73);
insert into shopping_cart (userID, gameID, game_price) values (39, 43, 153);
insert into shopping_cart (userID, gameID, game_price) values (40, 35, 176);
insert into shopping_cart (userID, gameID, game_price) values (41, 4, 97);
insert into shopping_cart (userID, gameID, game_price) values (42, 3, 94);
insert into shopping_cart (userID, gameID, game_price) values (43, 42, 78);
insert into shopping_cart (userID, gameID, game_price) values (44, 10, 33);
insert into shopping_cart (userID, gameID, game_price) values (45, 22, 92);
insert into shopping_cart (userID, gameID, game_price) values (46, 11, 158);
insert into shopping_cart (userID, gameID, game_price) values (47, 33, 47);
insert into shopping_cart (userID, gameID, game_price) values (48, 21, 187);
insert into shopping_cart (userID, gameID, game_price) values (49, 20, 100);
insert into shopping_cart (userID, gameID, game_price) values (50, 2, 193);

-- Populate Table order_
insert into order_ (userID, type, state, value, order_date) values (1, 'MasterCard', true, 40, '2018-09-07');
insert into order_ (userID,  type, state, value, order_date) values (2, 'Visa', true, 195, '2018-07-05');
insert into order_ (userID,  type, state, value, order_date) values (3, 'Visa', false, 82, '2019-11-23');
insert into order_ (userID,  type, state, value, order_date) values (4, 'PayPal', false, 57, '2018-11-13');
insert into order_ (userID,  type, state, value, order_date) values (5, 'Visa', false, 34, '2021-11-10');
insert into order_ (userID,  type, state, value, order_date) values (6, 'Visa', true, 198, '2020-07-27');
insert into order_ (userID,  type, state, value, order_date) values (7, 'MasterCard', true, 127, '2022-03-22');
insert into order_ (userID,  type, state, value, order_date) values (8, 'Visa', false, 12, '2020-02-12');
insert into order_ (userID,  type, state, value, order_date) values (9, 'Visa', false, 89, '2019-08-14');
insert into order_ (userID,  type, state, value, order_date) values (10, 'Visa', false, 76, '2021-03-20');
insert into order_ (userID,  type, state, value, order_date) values (11, 'Visa', false, 115, '2022-10-03');
insert into order_ (userID,  type, state, value, order_date) values (12, 'PayPal', true, 131, '2021-07-19');
insert into order_ (userID,  type, state, value, order_date) values (13, 'MasterCard', false, 161, '2018-10-09');
insert into order_ (userID,  type, state, value, order_date) values (14, 'Visa', false, 178, '2018-11-11');
insert into order_ (userID,  type, state, value, order_date) values (15, 'MasterCard', false, 75, '2021-04-04');
insert into order_ (userID,  type, state, value, order_date) values (16, 'MasterCard', true, 104, '2018-04-08');
insert into order_ (userID,  type, state, value, order_date) values (17, 'Visa', true, 18, '2020-06-19');
insert into order_ (userID,  type, state, value, order_date) values (18, 'MasterCard', true, 129, '2019-08-21');
insert into order_ (userID,  type, state, value, order_date) values (19, 'PayPal', true, 53, '2019-04-12');
insert into order_ (userID,  type, state, value, order_date) values (20, 'Visa', false, 118, '2020-02-14');
insert into order_ (userID,  type, state, value, order_date) values (21, 'PayPal', false, 44, '2020-01-13');
insert into order_ (userID,  type, state, value, order_date) values (22, 'MasterCard', false, 33, '2021-04-05');
insert into order_ (userID,  type, state, value, order_date) values (23, 'Visa', false, 20, '2018-01-02');
insert into order_ (userID,  type, state, value, order_date) values (24, 'MasterCard', true, 2, '2019-07-30');
insert into order_ (userID,  type, state, value, order_date) values (25, 'PayPal', true, 154, '2018-03-15');
insert into order_ (userID,  type, state, value, order_date) values (26, 'PayPal', false, 76, '2020-08-07');
insert into order_ (userID,  type, state, value, order_date) values (27, 'MasterCard', false, 158, '2022-10-10');
insert into order_ (userID,  type, state, value, order_date) values (28, 'Visa', false, 38, '2021-05-29');
insert into order_ (userID,  type, state, value, order_date) values (29, 'MasterCard', true, 95, '2019-02-07');
insert into order_ (userID,  type, state, value, order_date) values (30, 'MasterCard', true, 43, '2018-09-04');
insert into order_ (userID,  type, state, value, order_date) values (31, 'PayPal', true, 67, '2019-09-09');
insert into order_ (userID,  type, state, value, order_date) values (32, 'PayPal', false, 64, '2019-07-10');
insert into order_ (userID,  type, state, value, order_date) values (33, 'MasterCard', false, 183, '2021-07-07');
insert into order_ (userID,  type, state, value, order_date) values (34, 'Visa', false, 39, '2021-09-06');
insert into order_ (userID,  type, state, value, order_date) values (35, 'Visa', true, 199, '2020-12-08');
insert into order_ (userID,  type, state, value, order_date) values (36, 'MasterCard', false, 1, '2021-04-25');
insert into order_ (userID,  type, state, value, order_date) values (37, 'PayPal', false, 114, '2020-12-25');
insert into order_ (userID,  type, state, value, order_date) values (38, 'Visa', true, 183, '2020-11-26');
insert into order_ (userID,  type, state, value, order_date) values (39, 'MasterCard', false, 182, '2022-07-17');
insert into order_ (userID,  type, state, value, order_date) values (40, 'MasterCard', false, 200, '2019-09-15');
insert into order_ (userID,  type, state, value, order_date) values (41, 'MasterCard', false, 117, '2018-09-20');
insert into order_ (userID,  type, state, value, order_date) values (42, 'Visa', true, 1, '2021-09-09');
insert into order_ (userID,  type, state, value, order_date) values (43, 'Visa', true, 89, '2018-03-21');
insert into order_ (userID,  type, state, value, order_date) values (44, 'PayPal', true, 74, '2022-07-11');
insert into order_ (userID,  type, state, value, order_date) values (45, 'MasterCard', false, 130, '2017-06-27');
insert into order_ (userID,  type, state, value, order_date) values (46, 'Visa', true, 188, '2019-06-27');
insert into order_ (userID,  type, state, value, order_date) values (47, 'MasterCard', true, 113, '2020-10-27');
insert into order_ (userID,  type, state, value, order_date) values (48, 'PayPal', true, 24, '2020-04-15');
insert into order_ (userID,  type, state, value, order_date) values (49, 'PayPal', false, 46, '2021-05-20');
insert into order_ (userID,  type, state, value, order_date) values (50, 'PayPal', false, 21, '2021-12-13');

-- Populate Table game_order
insert into game_order (orderID, gameID, price) values (1, 24, 18);
insert into game_order (orderID, gameID, price) values (2, 26, 52);
insert into game_order (orderID, gameID, price) values (3, 25, 3);
insert into game_order (orderID, gameID, price) values (4, 41, 66);
insert into game_order (orderID, gameID, price) values (5, 2, 65);
insert into game_order (orderID, gameID, price) values (6, 37, 40);
insert into game_order (orderID, gameID, price) values (7, 24, 77);
insert into game_order (orderID, gameID, price) values (8, 26, 55);
insert into game_order (orderID, gameID, price) values (9, 30, 30);
insert into game_order (orderID, gameID, price) values (10, 47, 99);
insert into game_order (orderID, gameID, price) values (11, 37, 99);
insert into game_order (orderID, gameID, price) values (12, 46, 69);
insert into game_order (orderID, gameID, price) values (13, 5, 81);
insert into game_order (orderID, gameID, price) values (14, 44, 42);
insert into game_order (orderID, gameID, price) values (15, 3, 54);
insert into game_order (orderID, gameID, price) values (16, 46, 66);
insert into game_order (orderID, gameID, price) values (17, 28, 93);
insert into game_order (orderID, gameID, price) values (18, 2, 9);
insert into game_order (orderID, gameID, price) values (19, 8, 100);
insert into game_order (orderID, gameID, price) values (20, 9, 92);
insert into game_order (orderID, gameID, price) values (21, 13, 24);
insert into game_order (orderID, gameID, price) values (22, 21, 77);
insert into game_order (orderID, gameID, price) values (23, 35, 69);
insert into game_order (orderID, gameID, price) values (24, 18, 93);
insert into game_order (orderID, gameID, price) values (25, 14, 7);
insert into game_order (orderID, gameID, price) values (26, 27, 94);
insert into game_order (orderID, gameID, price) values (27, 45, 89);
insert into game_order (orderID, gameID, price) values (28, 34, 75);
insert into game_order (orderID, gameID, price) values (29, 5, 71);
insert into game_order (orderID, gameID, price) values (30, 10, 46);
insert into game_order (orderID, gameID, price) values (31, 28, 15);
insert into game_order (orderID, gameID, price) values (32, 31, 92);
insert into game_order (orderID, gameID, price) values (33, 28, 50);
insert into game_order (orderID, gameID, price) values (34, 43, 25);
insert into game_order (orderID, gameID, price) values (35, 22, 26);
insert into game_order (orderID, gameID, price) values (36, 19, 27);
insert into game_order (orderID, gameID, price) values (37, 1, 94);
insert into game_order (orderID, gameID, price) values (38, 25, 34);
insert into game_order (orderID, gameID, price) values (39, 6, 96);
insert into game_order (orderID, gameID, price) values (40, 38, 29);
insert into game_order (orderID, gameID, price) values (41, 4, 40);
insert into game_order (orderID, gameID, price) values (42, 38, 36);
insert into game_order (orderID, gameID, price) values (43, 24, 57);
insert into game_order (orderID, gameID, price) values (44, 32, 35);
insert into game_order (orderID, gameID, price) values (45, 47, 2);
insert into game_order (orderID, gameID, price) values (46, 48, 57);
insert into game_order (orderID, gameID, price) values (47, 5, 35);
insert into game_order (orderID, gameID, price) values (48, 24, 84);
insert into game_order (orderID, gameID, price) values (49, 19, 25);
insert into game_order (orderID, gameID, price) values (50, 38, 95);

-- Populate Table notification
insert into notification (userID, read_status, type) values (6, false, 'Wishlist');
insert into notification (userID, read_status, type) values (10, true, 'Reviewed');
insert into notification (userID, read_status, type) values (47, false, 'Wishlist');
insert into notification (userID, read_status, type) values (37, false, 'Reviewed');
insert into notification (userID, read_status, type) values (6, false, 'Wishlist');
insert into notification (userID, read_status, type) values (14, false, 'Reviewed');
insert into notification (userID, read_status, type) values (13, true, 'Reviewed');
insert into notification (userID, read_status, type) values (42, true, 'Wishlist');
insert into notification (userID, read_status, type) values (15, false, 'Reviewed');
insert into notification (userID, read_status, type) values (9, true, 'Reviewed');
insert into notification (userID, read_status, type) values (31, true, 'Wishlist');
insert into notification (userID, read_status, type) values (9, false, 'Reviewed');
insert into notification (userID, read_status, type) values (34, true, 'Wishlist');
insert into notification (userID, read_status, type) values (27, false, 'Wishlist');
insert into notification (userID, read_status, type) values (9, true, 'Wishlist');
insert into notification (userID, read_status, type) values (49, true, 'Wishlist');
insert into notification (userID, read_status, type) values (36, true, 'Wishlist');
insert into notification (userID, read_status, type) values (22, false, 'Wishlist');
insert into notification (userID, read_status, type) values (19, true, 'Reviewed');
insert into notification (userID, read_status, type) values (4, true, 'Wishlist');
insert into notification (userID, read_status, type) values (4, false, 'Wishlist');
insert into notification (userID, read_status, type) values (7, false, 'Wishlist');
insert into notification (userID, read_status, type) values (2, false, 'Reviewed');
insert into notification (userID, read_status, type) values (3, true, 'Reviewed');
insert into notification (userID, read_status, type) values (46, true, 'Wishlist');
insert into notification (userID, read_status, type) values (10, false, 'Wishlist');
insert into notification (userID, read_status, type) values (49, true, 'Reviewed');
insert into notification (userID, read_status, type) values (29, false, 'Wishlist');
insert into notification (userID, read_status, type) values (43, false, 'Wishlist');
insert into notification (userID, read_status, type) values (36, true, 'Wishlist');
insert into notification (userID, read_status, type) values (16, true, 'Reviewed');
insert into notification (userID, read_status, type) values (19, true, 'Wishlist');
insert into notification (userID, read_status, type) values (45, false, 'Wishlist');
insert into notification (userID, read_status, type) values (19, true, 'Wishlist');
insert into notification (userID, read_status, type) values (18, false, 'Reviewed');
insert into notification (userID, read_status, type) values (36, false, 'Wishlist');
insert into notification (userID, read_status, type) values (38, false, 'Wishlist');
insert into notification (userID, read_status, type) values (45, false, 'Wishlist');
insert into notification (userID, read_status, type) values (1, true, 'Reviewed');
insert into notification (userID, read_status, type) values (42, true, 'Wishlist');
insert into notification (userID, read_status, type) values (8, true, 'Reviewed');
insert into notification (userID, read_status, type) values (12, false, 'Wishlist');
insert into notification (userID, read_status, type) values (4, true, 'Wishlist');
insert into notification (userID, read_status, type) values (17, true, 'Wishlist');
insert into notification (userID, read_status, type) values (20, false, 'Reviewed');
insert into notification (userID, read_status, type) values (18, false, 'Wishlist');
insert into notification (userID, read_status, type) values (40, true, 'Reviewed');
insert into notification (userID, read_status, type) values (33, true, 'Wishlist');
insert into notification (userID, read_status, type) values (5, false, 'Reviewed');
insert into notification (userID, read_status, type) values (44, true, 'Wishlist');
