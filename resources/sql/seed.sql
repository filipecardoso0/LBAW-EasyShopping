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
	release_date TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL,
	classification INTEGER NOT NULL CONSTRAINT classification_ck CHECK (((classification >= 0) AND (classification <= 5))),
	discount NUMERIC(3,2) CONSTRAINT discount_ck CHECK (((discount >= 0) AND (discount <= 1))),
	approved BOOLEAN DEFAULT false NOT NULL
);

CREATE TABLE game_categories(
    gameID INTEGER NOT NULL REFERENCES game (gameID) ON UPDATE CASCADE,
    categoryID INTEGER NOT NULL REFERENCES category (categoryID) ON UPDATE CASCADE,
    PRIMARY KEY(gameID, categoryID)
);

CREATE TABLE review (
	reviewID SERIAL PRIMARY KEY,
	userID INTEGER NOT NULL REFERENCES users (userID) ON UPDATE CASCADE,
	gameID INTEGER NOT NULL REFERENCES game (gameID) ON UPDATE CASCADE,
	date TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL,
	comment TEXT NOT NULL,
	rating INTEGER NOT NULL CONSTRAINT rating_ck CHECK (((rating > 0) AND (rating <= 5))),
    status BOOLEAN NOT NULL DEFAULT false
);

CREATE TABLE wishlist (
    id SERIAL NOT NULL,
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

/* TODO FINALIZE TICKET SYSTEM LATER -> WE SURELY HAVE TO CREATE ANOTHER TABLE FOR TICKETS RESPONSES */
CREATE TABLE tickets(
    ticketID SERIAL PRIMARY KEY,
    userID INTEGER NOT NULL REFERENCES users (userID) ON DELETE CASCADE ON UPDATE CASCADE,
    create_date TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL,
    ticketstatus BOOLEAN DEFAULT false NOT NULL
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
-- TODO FIX THIS FUNCTION
-- Sends a notification to the user that is the gamepublisher of the game reviewed
/*
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

 */

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

--TODO FIX THIS FUNCTION
-- It is not possible to review a game that hasn't been launched yet
/*
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
*/


-- It is not possible to review a game that the user hasn't been bought yet
-- TODO FIX THIS FUNCTION
/*
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
*/

-- TODO FIX THIS FUNCTION
-- When a new review on a certain game is posted the game classification is updated
/*
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
 */

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
	INSERT INTO game_order (userid, gameID, game_price)
	(SELECT userid, gameID, game_price FROM shopping_cart WHERE userID = userIdentifier);
	-- Deletes games from shopping cart
	DELETE FROM shopping_cart WHERE userID = userIdentifier;
END;
$$
language plpgsql;

-----------------------------------------
-- Populate
-----------------------------------------

-- Populate Users Table
-- TODO FIX PUBLISHER NAMES ACCORDING TO GAME'S NAME
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
insert into game (userID, title, description, price, release_date, classification, discount, approved) values (18, 'Rocket League', 'Download and compete in the high-octane hybrid of arcade-style soccer and vehicular mayhem! customize your car, hit the field, and compete in one of the most critically acclaimed sports games of all time! Download and take your shot! <br/> Hit the field by yourself or with friends in 1v1, 2v2, and 3v3 Online Modes, or enjoy Extra Modes like Rumble, Snow Day, or Hoops. Unlock items in Rocket Pass, climb the Competitive Ranks, compete in Competitive Tournaments, complete Challenges, enjoy cross-platform progression and more! The field is waiting. Take your shot!', 0, '2015-07-07', 2, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (3, 'Grand Theft Auto V', 'When a young street hustler, a retired bank robber and a terrifying psychopath find themselves entangled with some of the most frightening and deranged elements of the criminal underworld, the U.S. government and the entertainment industry, they must pull off a series of dangerous heists to survive in a ruthless city in which they can trust nobody, least of all each other. <br/> Grand Theft Auto V for PC offers players the option to explore the award-winning world of Los Santos and Blaine County in resolutions of up to 4k and beyond, as well as the chance to experience the game running at 60 frames per second. <br/> The game offers players a huge range of PC-specific customization options, including over 25 separate configurable settings for texture quality, shaders, tessellation, anti-aliasing and more, as well as support and extensive customization for mouse and keyboard controls. Additional options include a population density slider to control car and pedestrian traffic, as well as dual and triple monitor support, 3D compatibility, and plug-and-play controller support. <br/> Grand Theft Auto V for PC also includes Grand Theft Auto Online, with support for 30 players and two spectators. Grand Theft Auto Online for PC will include all existing gameplay upgrades and Rockstar-created content released since the launch of Grand Theft Auto Online, including Heists and Adversary modes. <br/> The PC version of Grand Theft Auto V and Grand Theft Auto Online features First Person Mode, giving players the chance to explore the incredibly detailed world of Los Santos and Blaine County in an entirely new way. <br/> Grand Theft Auto V for PC also brings the debut of the Rockstar Editor, a powerful suite of creative tools to quickly and easily capture, edit and share game footage from within Grand Theft Auto V and Grand Theft Auto Online. The Rockstar Editor’s Director Mode allows players the ability to stage their own scenes using prominent story characters, pedestrians, and even animals to bring their vision to life. Along with advanced camera manipulation and editing effects including fast and slow motion, and an array of camera filters, players can add their own music using songs from GTAV radio stations, or dynamically control the intensity of the game’s score. Completed videos can be uploaded directly from the Rockstar Editor to YouTube and the Rockstar Games Social Club for easy sharing. <br/> Soundtrack artists The Alchemist and Oh No return as hosts of the new radio station, The Lab FM. The station features new and exclusive music from the production duo based on and inspired by the game’s original soundtrack. Collaborating guest artists include Earl Sweatshirt, Freddie Gibbs, Little Dragon, Killer Mike, Sam Herring from Future Islands, and more. Players can also discover Los Santos and Blaine County while enjoying their own music through Self Radio, a new radio station that will host player-created custom soundtracks. <br/> Special access content requires Rockstar Games Social Club account. Visit http://rockstargames.com/v/bonuscontent for details.', 15, '2015-04-14', 3, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (13, 'The Elder Scrolls V: Skyrim', 'The Elder Scrolls V: Skyrim, the 2011 Game of the Year, is the next chapter in the highly anticipated Elder Scrolls saga. Developed by Bethesda Game Studios, the 2011 Studio of the Year, that brought you Oblivion and Fallout 3. Skyrim reimagines and revolutionizes the open-world fantasy epic, bringing to life a complete virtual world open for you to explore any way you choose', 9, '2011-11-11', 1, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (37, 'Counter-Strike 1.6', 'Play the world''s number 1 online action game. Engage in an incredibly realistic brand of terrorist warfare in this wildly popular team-based game. Ally with teammates to complete strategic missions. Take out enemy sites. Rescue hostages. Your role affects your team''s success. Your team''s success affects your role.', 8, '2000-11-01', 2, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (32, 'Counter-Strike: Source', 'THE NEXT INSTALLMENT OF THE WORLD''S # 1 ONLINE ACTION GAMECounter-Strike: Source blends Counter-Strike''s award-winning teamplay action with the advanced technology of Source™ technology. Featuring state of the art graphics, all new sounds, and introducing physics, Counter-Strike: Source is a must-have for every action gamer.', 10, '2004-11-01', 5, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (4, 'Counter-Strike: Global Offensive', 'Counter-Strike: Global Offensive (CS: GO) expands upon the team-based action gameplay that it pioneered when it was launched 19 years ago. <br/> CS: GO features new maps, characters, weapons, and game modes, and delivers updated versions of the classic CS content (de_dust2, etc.). <br/> "Counter-Strike took the gaming industry by surprise when the unlikely MOD became the most played online PC action game in the world almost immediately after its release in August 1999," said Doug Lombardi at Valve. "For the past 12 years, it has continued to be one of the most-played games in the world, headline competitive gaming tournaments and selling over 25 million units worldwide across the franchise. CS: GO promises to expand on CS'' award-winning gameplay and deliver it to gamers on the PC as well as the next gen consoles and the Mac."', 0, '2012-08-21', 1, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (35, 'Forza Horizon 3', 'Xbox One X Enhanced with native 4K support. HDR enhanced on Xbox One S and Xbox One X with supported TVs. Supports Xbox Play Anywhere: yours to play on both Xbox One and Windows 10 PC at no additional cost. <br/> THIS IS YOUR HORIZON You’re in charge of the Horizon Festival. Customize everything, hire and fire your friends, and explore Australia in over 350 of the world’s greatest cars. Make your Horizon the ultimate celebration of cars, music, and freedom of the open road. How you get there is up to you. <br/> EXPLORE AUSTRALIA, HORIZON’S LARGEST WORLD EVER Drive through the vast desert and rocky canyons of the Outback to lush, wild rainforests, and to the sandy beaches and gleaming skyscrapers of Australia''s Gold Coast. <br/> CHOOSE FROM OVER 350 OF THE WORLD’S GREATEST CARS Every car is recreated with ForzaVista™ detail including full cockpit views, working lights and wipers, and new vehicle types bringing all-new driving experiences never before seen in Forza.', 12, '2016-09-27', 1, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (4, 'Forza Horizon 4', 'Dynamic seasons change everything at the world’s greatest automotive festival. Go it alone or team up with others to explore beautiful and historic Britain in a shared open world. Collect, modify and drive over 450 cars. Race, stunt, create and explore – choose your own path to become a Horizon Superstar. <br/> Collect Over 450 Cars Enjoy the largest and most diverse Horizon car roster yet, including over 100 licensed manufacturers. <br/> Race. Stunt. Create. Explore.In the new open-ended campaign, everything you do progresses your game. <br/> Explore a Shared World Real players populate your world. When time of day, weather and seasons change, everyone playing the game experiences it at the same time.', 70, '2018-09-28', 5, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (42, 'Forza Horizon 5', 'Your Ultimate Horizon Adventure awaits! Explore the vibrant and ever-evolving open world landscapes of Mexico with limitless, fun driving action in hundreds of the world’s greatest cars. <br/> This is Your Horizon Adventure Lead breathtaking expeditions across the vibrant and ever-evolving open world landscapes of Mexico with limitless, fun driving action in hundreds of the world’s greatest cars. <br/> This is a Diverse Open World Explore a world of striking contrast and beauty. Discover living deserts, lush jungles, historic cities, hidden ruins, pristine beaches, vast canyons and a towering snow-capped volcano. <br/> This is an Adventurous Open WorldImmerse yourself in a deep campaign with hundreds of challenges that reward you for engaging in the activities you love. Meet new characters and choose the outcomes of their Horizon Story missions.', 60, '2021-11-01', 3, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (14, 'Call of Duty: Modern Warfare 2 (2009)', 'The most-anticipated game of the year and the sequel to the best-selling first-person action game of all time, Modern Warfare 2 continues the gripping and heart-racing action as players face off against a new threat dedicated to bringing the world to the brink of collapse. Call of Duty®: Modern Warfare 2 features for the first time in video games, the musical soundtrack of legendary Academy Award®, Golden Globe® Award, Grammy® Award and Tony winning composer Hans Zimmer. The title picks up immediately following the historic events of Call of Duty® 4: Modern Warfare®, the blockbuster title that earned worldwide critical acclaim', 19, '2009-11-12', 1, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (24, 'Call of Duty: Black Ops', 'The biggest first-person action series of all time and the follow-up to last year’s blockbuster Call of Duty®: Modern Warfare 2 returns with Call of Duty®: Black Ops. Call of Duty®: Black Ops will take you behind enemy lines as a member of an elite special forces unit engaging in covert warfare, classified operations, and explosive conflicts across the globe. With access to exclusive weaponry and equipment, your actions will tip the balance during the most dangerous time period mankind has ever known.', 40, '2010-11-09', 3, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (44, 'Call of Duty: Black Ops II', 'Pushing the boundaries of what fans have come to expect from the record-setting entertainment franchise, Call of Duty®: Black Ops II propels players into a near future, 21st Century Cold War, where technology and weapons have converged to create a new generation of warfare.', 50, '2012-11-12', 4, 0.26, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (10, 'Call of Duty: Modern Warfare 3', 'The best-selling first person action series of all-time returns with the epic sequel to multiple “Game of the Year” award winner, Call of Duty®: Modern Warfare® 2. In the world’s darkest hour, are you willing to do what is necessary? Prepare yourself for a cinematic thrill-ride only Call of Duty can deliver. The definitive Multiplayer experience returns bigger and better than ever, loaded with new maps, modes and features. Co-Op play has evolved with all-new Spec-Ops missions and leaderboards, as well as Survival Mode, an action-packed combat progression unlike any other.', 40, '2011-11-08', 5, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (18, 'Call of Duty: Modern Warfare (2019)', 'Prepare to go dark in Call of Duty®: Modern Warfare® <br/> The stakes have never been higher as players take on the role of lethal Tier One Operators in a heart-racing saga that will affect the global balance of power. Developed by the studio that started it all, Infinity Ward delivers an epic reimagining of the iconic Modern Warfare® series from the ground up.', 60, '2019-10-25', 3, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (9, 'Call of Duty: Modern Warfare 2 (2022)', 'Call of Duty®: Modern Warfare® II drops players into an unprecedented global conflict that features the return of the iconic Operators of Task Force 141. From small-scale, high-stakes infiltration tactical ops to highly classified missions, players will deploy alongside friends in a truly immersive experience. <br/> Infinity Ward brings fans state-of-the-art gameplay, with all-new gun handling, advanced AI system, a new Gunsmith and a suite of other gameplay and graphical innovations that elevate the franchise to new heights. <br/> Modern Warfare® II launches with a globe-trotting single-player campaign, immersive Multiplayer combat and a narrative-driven, co-op Special Ops experience. <br/> You also get access to Call of Duty®: Warzone™ 2.0, the all-new Battle Royale experience.', 70, '2022-10-28', 4, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (10, 'Call of Duty: Cold War', 'The iconic Black Ops series is back with Call of Duty®: Black Ops Cold War <br/> Welcome to the brink. Welcome to Call of Duty®: Black Ops Cold War - the direct sequel to the original and fan-favorite Call of Duty®: Black Ops.', 60, '2020-11-13', 3, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (19, 'Call of Duty: Vanguard', 'The Call of Duty® franchise returns with Call of Duty®: Vanguard <br/> Rise on every front: Dogfight over the Pacific, airdrop over France, defend Stalingrad with a sniper’s precision and blast through enemies in North Africa.', 60, '2021-11-05', 2, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (43, 'Battlefield 2042', 'WELCOME TO 2042 <br/> Battlefield™ 2042 is a first-person shooter that marks the return to the iconic all-out warfare of the franchise. Adapt and overcome in a near-future world transformed by disorder. Squad up and bring a cutting-edge arsenal into dynamically changing battlegrounds supporting 128 players, unprecedented scale, and epic destruction.', 60, '2021-11-19', 4, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (50, 'Battlefield V', 'This is the ultimate Battlefield V experience. Enter mankind’s greatest conflict across land, air, and sea with all gameplay content unlocked from the get-go. Choose from the complete arsenal of weapons, vehicles, and gadgets, and immerse yourself in the hard-fought battles of World War II. Stand out on the battlefield with the complete roster of Elites and the best customization content of Year 1 and Year 2.', 50, '2018-11-09', 4, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (25, 'Battlefield 3', 'Ramp up the intensity in Battlefield™ 3 and enjoy total freedom to fight the way you want. Explore 29 massive multiplayer maps and use loads of vehicles, weapons, and gadgets to help you turn up the heat. Plus, every second of battle gets you closer to unlocking tons of extras and moving up in the Ranks. So get in the action.', 40, '2011-10-28', 3, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (34, 'Minecraft', 'Create, explore, survive, repeat. Get Minecraft: Java Edition and Bedrock Edition as a package deal for Windows! With Minecraft: Java & Bedrock Edition for Windows, you can easily switch between games using the unified launcher and cross-play with any current edition of Minecraft.', 30, '2011-11-18', 2, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (49, 'Roblox', 'Roblox is an online game platform and game creation system developed by Roblox Corporation that allows users to program games and play games created by other users.', 0, '2006-09-01', 1, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (39, 'Hearthstone', 'Hearthstone is a free-to-play online digital collectible card game developed and published by Blizzard Entertainment. Originally subtitled Heroes of Warcraft, Hearthstone builds upon the existing lore of the Warcraft series by using the same elements, characters, and relics.', 0, '2014-03-11', 2, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (38, 'Spider-Man (2018)', 'Developed by Insomniac Games in collaboration with Marvel, and optimized for PC by Nixxes Software, Marvel''s Spider-Man Remastered on PC introduces an experienced Peter Parker who’s fighting big crime and iconic villains in Marvel’s New York. At the same time, he’s struggling to balance his chaotic personal life and career while the fate of Marvel’s New York rests upon his shoulders.', 20, '2018-09-07', 3, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (18, 'Marvel''s Spider-Man: Miles Morales', 'Following the events of Marvel’s Spider-Man Remastered, teenager Miles Morales is adjusting to his new home while following in the footsteps of his mentor, Peter Parker, as a new Spider-Man. But when a fierce power struggle threatens to destroy his new home, the aspiring hero realizes that with great power, there must also come great responsibility. To save all of Marvel’s New York, Miles must take up the mantle of Spider-Man and own it.', 50, '2020-11-12', 1, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (33, 'Resident Evil Village', 'Experience survival horror like never before in the eighth major installment in the storied Resident Evil franchise - Resident Evil Village. <br/> Set a few years after the horrifying events in the critically acclaimed Resident Evil 7 biohazard, the all-new storyline begins with Ethan Winters and his wife Mia living peacefully in a new location, free from their past nightmares. Just as they are building their new life together, tragedy befalls them once again.', 20, '2021-05-07', 1, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (42, 'Tom Clancy''s Rainbow Six® Siege', 'Master the art of destruction and gadgetry in Tom Clancy’s Rainbow Six Siege. Face intense close quarters combat, high lethality, tactical decision making, team play and explosive action within every moment. Experience a new era of fierce firefights and expert strategy born from the rich legacy of past Tom Clancy''s Rainbow Six games. <br/> Engage in a brand-new style of assault using an unrivaled level of destruction and gadgetry. <br/> On defense, coordinate with your team to transform your environments into strongholds. Trap, fortify and create defensive systems to prevent being breached by the enemy. <br/> On attack, lead your team through narrow corridors, barricaded doorways and reinforced walls. Combine tactical maps, observation drones, rappelling and more to plan, attack and defuse every situation. <br/> Choose from dozens of highly trained, Special Forces operators from around the world. Deploy the latest technology to track enemy movement. Shatter walls to open new lines of fire. Breach ceilings and floors to create new access points. Employ every weapon and gadget from your deadly arsenal to locate, manipulate and destroy your enemies and the environment around them. <br/> Experience new strategies and tactics as Rainbow Six Siege evolves over time. Change the rules of Siege with every update that includes new operators, weapons, gadgets and maps. Evolve alongside the ever-changing landscape with your friends and become the most experienced and dangerous operators out there. <br/> Compete against others from around the world in ranked match play. Grab your best squad and join the competitive community in weekly tournaments or watch the best professional teams battle it out in the Rainbow Six Siege Pro League.', 5, '2015-12-01', 1, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (2, 'Cyberpunk 2077', 'Cyberpunk 2077 is an open-world, action-adventure RPG set in the megalopolis of Night City, where you play as a cyberpunk mercenary wrapped up in a do-or-die fight for survival. Improved and featuring all-new free additional content, customize your character and playstyle as you take on jobs, build a reputation, and unlock upgrades. The relationships you forge and the choices you make will shape the story and the world around you. Legends are made here. What will yours be?', 60, '2020-12-10', 4, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (41, 'Overwatch 2', 'Overwatch 2 is a free-to-play, team-based action game set in the optimistic future, where every match is the ultimate 5v5 battlefield brawl. Play as a time-jumping freedom fighter, a beat-dropping battlefield DJ, or one of over 30 other unique heroes as you battle it out around the globe.  ', 0, '2022-10-04', 1, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (21, 'PlayerUnknown''s Battlegrounds', 'Land on strategic locations, loot weapons and supplies, and survive to become the last team standing across various, diverse Battlegrounds. <br/> Squad up and join the Battlegrounds for the original Battle Royale experience that only PUBG: BATTLEGROUNDS can offer.', 0, '2017-03-23', 3, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (19, 'Escape From Tarkov', 'Escape from Tarkov is a multiplayer tactical first-person shooter video game in development by Battlestate Games for Windows. The game is set in the fictional Norvinsk region, where a war is taking place between two private military companies.', 35, '2016-10-04', 3, 0.96, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (37, 'World of Warcraft', 'World of Warcraft is a massively multiplayer online role-playing game released in 2004 by Blizzard Entertainment.', 30, '2004-11-23', 4, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (20, 'Red Dead Redemption', 'Red Dead Redemption is a 2010 action-adventure game developed by Rockstar San Diego and published by Rockstar Games. A spiritual successor to 2004''s Red Dead Revolver, it is the second game in the Red Dead series.', 60, '2010-05-18', 3, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (46, 'Dark Souls: Remastered', 'Dark Souls is a series of action role-playing games created by Hidetaka Miyazaki of FromSoftware and published by Bandai Namco Entertainment. The series began with the release of Dark Souls in 2011 and has seen two sequels, Dark Souls II and Dark Souls III.', 40, '2011-09-22', 4, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (50, 'The Last of Us', 'The Last of Us is a 2013 action-adventure game developed by Naughty Dog and published by Sony Computer Entertainment. Players control Joel, a smuggler tasked with escorting a teenage girl, Ellie, across a post-apocalyptic United States. The Last of Us is played from a third-person perspective.', 70, '2013-06-14', 2, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (45, 'Uncharted 4: A Thief''s End', 'Uncharted 4: A Thief''s End is a 2016 action-adventure game developed by Naughty Dog and published by Sony Computer Entertainment. It is the fourth main entry in the Uncharted series.', 50, '2016-05-10', 2, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (40, 'Uncharted: Drake''s Fortune', 'Uncharted: Drake''s Fortune is a 2007 action-adventure game developed by Naughty Dog and published by Sony Computer Entertainment. It is the first game in the Uncharted series and was released in November 2007 for PlayStation 3.', 5, '2007-11-19', 1, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (10, 'Uncharted 2: Among Thieves', 'Uncharted 2: Among Thieves is a 2009 action-adventure game developed by Naughty Dog and published by Sony Computer Entertainment. It is the second game in the Uncharted series and was released in October 2009 for PlayStation 3.', 5, '2009-10-13', 4, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (40, 'Uncharted 3: Drake''s Deception', 'Uncharted 3: Drake''s Deception is a 2011 action-adventure game developed by Naughty Dog and published by Sony Computer Entertainment. It is the third main entry in the Uncharted series and was released in November 2011 for the PlayStation 3.', 5, '2011-11-01', 5, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (13, 'The Last of Us Part II', 'The Last of Us Part II is a 2020 action-adventure game developed by Naughty Dog and published by Sony Interactive Entertainment for the PlayStation 4.', 40, '2020-06-19', 3, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (42, 'Days Gone', 'Days Gone is a 2019 action-adventure video game developed by Bend Studio and published by Sony Interactive Entertainment for the PlayStation 4. A Microsoft Windows port was released in May 2021.', 50, '2021-05-18', 5, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (46, 'Left 4 Dead', 'From Valve (the creators of Counter-Strike, Half-Life and more) comes Left 4 Dead, a co-op action horror game for the PC and Xbox 360 that casts up to four players in an epic struggle for survival against swarming zombie hordes and terrifying mutant monsters. <br/> Set in the immediate aftermath of the zombie apocalypse, L4D''s survival co-op mode lets you blast a path through the infected in four unique “movies,” guiding your survivors across the rooftops of an abandoned metropolis, through rural ghost towns and pitch-black forests in your quest to escape a devastated Ground Zero crawling with infected enemies. Each "movie" is comprised of five large maps, and can be played by one to four human players, with an emphasis on team-based strategy and objectives. <br/> New technology dubbed "the AI Director" is used to generate a unique gameplay experience every time you play. The Director tailors the frequency and ferocity of the zombie attacks to your performance, putting you in the middle of a fast-paced, but not overwhelming, Hollywood horror movie.', 10, '2008-11-17', 4, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (14, 'Grand Theft Auto III', 'Grand Theft Auto III is a 2001 action-adventure game developed by DMA Design and published by Rockstar Games. It is the third main entry in the Grand Theft Auto series, following 1999''s Grand Theft Auto 2, and the fifth instalment overall.', 5, '2001-10-22', 2, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (10, 'Assassin''s Creed', 'Assassin''s Creed™ is the next-gen game developed by Ubisoft Montreal that redefines the action genre.', 8, '2007-11-13', 1, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (9, 'Grand Theft Auto: San Andreas', 'Grand Theft Auto: San Andreas is a 2004 action-adventure game developed by Rockstar North and published by Rockstar Games. It is the fifth main entry in the Grand Theft Auto series, following 2002''s Grand Theft Auto: Vice City, and the seventh installment overall.', 25, '2004-10-26', 1, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (3, 'The Sims', 'The Sims is a series of life simulation video games developed by Maxis and published by Electronic Arts. The franchise has sold nearly 200 million copies worldwide, and it is one of the best-selling video game series of all time.', 2, '2000-02-04', 2, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (14, 'FIFA 22', 'Powered by Football™, EA SPORTS™ FIFA 22 brings the game even closer to the real thing with fundamental gameplay advances and a new season of innovation across every mode. <br/> What is FIFA? <br/> Play The World’s Game with 17,000+ players, over 700 teams in 90+ stadiums and more than 30 leagues from all over the globe.', 60, '2021-09-30', 1, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (17, 'F1 22', 'Enter the new era of Formula 1® in EA SPORTS™ F1® 22, the official video game of the 2022 FIA Formula One World Championship™. Take your seat for a new season as redesigned cars and overhauled rules redefine race day, test your skills around the new Miami International Autodrome, and get a taste of the glitz and glamour in F1® Life. <br/> Race the stunning, new cars of the Formula 1® 2022 season with the authentic lineup of all 20 drivers and 10 teams, and take control of your race experience with new immersive or broadcast race sequences. Create a team and take them to the front of the grid with new depth in the acclaimed My Team career mode, race head-to-head in split-screen or multiplayer, or change the pace by taking supercars from some of the sport’s biggest names to the track in our all-new Pirelli Hot Laps feature. <br/> The official video game of the 2022 FIA Formula One World Championship™:', 60, '2022-07-01', 3, 0.00, true);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (13, 'NBA 2K23', 'Rise to the occasion and realize your full potential in NBA 2K23. Prove yourself against the best players in the world and showcase your talent in MyCAREER. Pair today’s All-Stars with timeless legends in MyTEAM. Build a dynasty of your own in MyGM or take the NBA in a new direction with MyLEAGUE. Take on NBA or WNBA teams in PLAY NOW and experience true-to-life gameplay. How will you Answer the Call? <br/> TAKE MORE CONTROL <br/> Feel refined gameplay in the palm of your hands on both sides of the ball in NBA 2K23. Attack the basket with a new arsenal of offensive skill-based moves, while you unleash your potential as a lockdown defender with new 1-on-1 mechanics to stifle opposing players at every turn. <br/> <br/> AN EPIC VOYAGE AWAITS <br/> Embark on a swashbuckling basketball journey aboard a spacious cruiseliner equipped with pristine courts, scenic views, and a boatload of rewards for you and your MyPLAYER to enjoy. Plus, there’s even more to explore during shore excursions. <br/> <br/> JORDAN CHALLENGE RETURNS <br/> Step back in time with era-specific visuals that captured Michael Jordan’s ascent from collegiate sensation to global icon with immersive Jordan Challenges chronicling his career-defining dominance. Lace up his shoes to recreate his otherworldly stat lines and iconic last shots, while listening to first-hand accounts from those who witnessed his maturation from budding star to basketball legend. <br/> <br/> BUILD YOUR SQUAD <br/> Ball without limits as you collect and assemble a bevy of legendary talent from any era in MyTEAM. Dominate the hardwood each Season, and bring your vision to life with a broad set of customization tools to create the perfect look for your perfect starting five.', 60, '2022-09-08', 4, 0.00, false);
insert into game (userID, title, description, price,  release_date, classification, discount, approved) values (37, 'Elden Ring', 'THE NEW FANTASY ACTION RPG. <br/> Rise, Tarnished, and be guided by grace to brandish the power of the Elden Ring and become an Elden Lord in the Lands Between. <br/> • A Vast World Full of Excitement <br/> A vast world where open fields with a variety of situations and huge dungeons with complex and three-dimensional designs are seamlessly connected. As you explore, the joy of discovering unknown and overwhelming threats await you, leading to a high sense of accomplishment. <br/> <br/> Create your Own Character <br/> In addition to customizing the appearance of your character, you can freely combine the weapons, armor, and magic that you equip. You can develop your character according to your play style, such as increasing your muscle strength to become a strong warrior, or mastering magic. <br/> <br/> An Epic Drama Born from a Myth <br/> A multilayered story told in fragments. An epic drama in which the various thoughts of the characters intersect in the Lands Between. <br/> <br/> Unique Online Play that Loosely Connects You to Others <br/> In addition to multiplayer, where you can directly connect with other players and travel together, the game supports a unique asynchronous online element that allows you to feel the presence of others.', 60, '2022-02-24', 5, 0.00, false);

-- Populate Table Game Categories
insert into game_categories (gameID, categoryID) values (1, 9);
insert into game_categories (gameID, categoryID) values (1, 2);
insert into game_categories (gameID, categoryID) values (2, 1);
insert into game_categories (gameID, categoryID) values (2, 10);
insert into game_categories (gameID, categoryID) values (3, 4);
insert into game_categories (gameID, categoryID) values (3, 3);
insert into game_categories (gameID, categoryID) values (4, 6);
insert into game_categories (gameID, categoryID) values (4, 5);
insert into game_categories (gameID, categoryID) values (5, 2);
insert into game_categories (gameID, categoryID) values (5, 6);
insert into game_categories (gameID, categoryID) values (6, 6);
insert into game_categories (gameID, categoryID) values (6, 7);
insert into game_categories (gameID, categoryID) values (7, 3);
insert into game_categories (gameID, categoryID) values (7, 8);
insert into game_categories (gameID, categoryID) values (8, 7);
insert into game_categories (gameID, categoryID) values (8, 9);
insert into game_categories (gameID, categoryID) values (9, 8);
insert into game_categories (gameID, categoryID) values (9, 10);
insert into game_categories (gameID, categoryID) values (10, 9);
insert into game_categories (gameID, categoryID) values (10, 8);
insert into game_categories (gameID, categoryID) values (11, 8);
insert into game_categories (gameID, categoryID) values (11, 7);
insert into game_categories (gameID, categoryID) values (12, 1);
insert into game_categories (gameID, categoryID) values (12, 6);
insert into game_categories (gameID, categoryID) values (13, 5);
insert into game_categories (gameID, categoryID) values (13, 4);
insert into game_categories (gameID, categoryID) values (14, 10);
insert into game_categories (gameID, categoryID) values (14, 3);
insert into game_categories (gameID, categoryID) values (15, 5);
insert into game_categories (gameID, categoryID) values (15, 2);
insert into game_categories (gameID, categoryID) values (16, 9);
insert into game_categories (gameID, categoryID) values (16, 1);
insert into game_categories (gameID, categoryID) values (17, 8);
insert into game_categories (gameID, categoryID) values (17, 2);
insert into game_categories (gameID, categoryID) values (18, 10);
insert into game_categories (gameID, categoryID) values (18, 3);
insert into game_categories (gameID, categoryID) values (19, 1);
insert into game_categories (gameID, categoryID) values (19, 4);
insert into game_categories (gameID, categoryID) values (20, 7);
insert into game_categories (gameID, categoryID) values (20, 5);
insert into game_categories (gameID, categoryID) values (21, 10);
insert into game_categories (gameID, categoryID) values (22, 4);
insert into game_categories (gameID, categoryID) values (23, 1);
insert into game_categories (gameID, categoryID) values (24, 6);
insert into game_categories (gameID, categoryID) values (25, 5);
insert into game_categories (gameID, categoryID) values (26, 7);
insert into game_categories (gameID, categoryID) values (27, 9);
insert into game_categories (gameID, categoryID) values (28, 9);
insert into game_categories (gameID, categoryID) values (29, 10);
insert into game_categories (gameID, categoryID) values (30, 9);
insert into game_categories (gameID, categoryID) values (31, 3);
insert into game_categories (gameID, categoryID) values (32, 7);
insert into game_categories (gameID, categoryID) values (33, 6);
insert into game_categories (gameID, categoryID) values (34, 1);
insert into game_categories (gameID, categoryID) values (35, 7);
insert into game_categories (gameID, categoryID) values (36, 8);
insert into game_categories (gameID, categoryID) values (37, 6);
insert into game_categories (gameID, categoryID) values (38, 5);
insert into game_categories (gameID, categoryID) values (39, 3);
insert into game_categories (gameID, categoryID) values (40, 3);
insert into game_categories (gameID, categoryID) values (41, 5);
insert into game_categories (gameID, categoryID) values (42, 8);
insert into game_categories (gameID, categoryID) values (43, 9);
insert into game_categories (gameID, categoryID) values (44, 9);
insert into game_categories (gameID, categoryID) values (45, 10);
insert into game_categories (gameID, categoryID) values (46, 4);
insert into game_categories (gameID, categoryID) values (47, 5);
insert into game_categories (gameID, categoryID) values (48, 8);
insert into game_categories (gameID, categoryID) values (49, 10);
insert into game_categories (gameID, categoryID) values (50, 2);

-- Populate Table Review
-- TODO FIX THIS INSERTS
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
