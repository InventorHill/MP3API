SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `file_locations`;
CREATE TABLE `file_locations` (
  `file_num` int(11) NOT NULL,
  `file_name` varchar(512) NOT NULL,
  `version` double NOT NULL DEFAULT '1',
  `server_dir` varchar(512) NOT NULL,
  `pi_dir` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `file_locations` (`file_num`, `file_name`, `version`, `server_dir`, `pi_dir`) VALUES
(1, 'mp3Player.py', 1.3, 'C:\\pi\\home\\pi\\mp3Player.py', '/home/pi/mp3Player.py'),
(2, 'mp3Player_old.py', 1, 'C:\\pi\\home\\pi\\mp3Player_old.py', '/home/pi/mp3Player_old.py'),
(3, 'player_run', 1, 'C:\\pi\\home\\pi\\player_run', '/home/pi/player_run'),
(4, 'HG_Wells_The_Time_Machine.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Audiobooks\\HG_Wells\\HG_Wells_The_Time_Machine.mp3', '/home/pi/MP3s/Audiobooks/HG_Wells/HG_Wells_The_Time_Machine.mp3'),
(5, 'HG_Wells_The_War_of_the_Worlds.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Audiobooks\\HG_Wells\\HG_Wells_The_War_of_the_Worlds.mp3', '/home/pi/MP3s/Audiobooks/HG_Wells/HG_Wells_The_War_of_the_Worlds.mp3'),
(6, 'Jonathan_Swift_Gullivers_Travels.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Audiobooks\\Jonathan_Swift\\Jonathan_Swift_Gullivers_Travels.mp3', '/home/pi/MP3s/Audiobooks/Jonathan_Swift/Jonathan_Swift_Gullivers_Travels.mp3'),
(7, 'Jules_Verne_20000_Leagues_Under_the_Sea.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Audiobooks\\Jules_Verne\\Jules_Verne_20000_Leagues_Under_the_Sea.mp3', '/home/pi/MP3s/Audiobooks/Jules_Verne/Jules_Verne_20000_Leagues_Under_the_Sea.mp3'),
(8, 'Jules_Verne_From_the_Earth_to_the_Moon.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Audiobooks\\Jules_Verne\\Jules_Verne_From_the_Earth_to_the_Moon.mp3', '/home/pi/MP3s/Audiobooks/Jules_Verne/Jules_Verne_From_the_Earth_to_the_Moon.mp3'),
(9, 'Jules_Verne_Round_the_Moon.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Audiobooks\\Jules_Verne\\Jules_Verne_Round_the_Moon.mp3', '/home/pi/MP3s/Audiobooks/Jules_Verne/Jules_Verne_Round_the_Moon.mp3'),
(10, 'Beethoven_Symphony_5.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Beethoven\\Beethoven_Symphony_5.mp3', '/home/pi/MP3s/Music/Beethoven/Beethoven_Symphony_5.mp3'),
(11, 'Borodin_In_the_Steppes_of_Central_Asia_Polovtsian_Dances.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Borodin\\Borodin_In_the_Steppes_of_Central_Asia_Polovtsian_Dances.mp3', '/home/pi/MP3s/Music/Borodin/Borodin_In_the_Steppes_of_Central_Asia_Polovtsian_Dances.mp3'),
(12, 'Copland_Rodeo.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Copland\\Copland_Rodeo.mp3', '/home/pi/MP3s/Music/Copland/Copland_Rodeo.mp3'),
(13, 'Dvorak_American_Quartet.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Dvorak\\Dvorak_American_Quartet.mp3', '/home/pi/MP3s/Music/Dvorak/Dvorak_American_Quartet.mp3'),
(14, 'Dvorak_American_Suite.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Dvorak\\Dvorak_American_Suite.mp3', '/home/pi/MP3s/Music/Dvorak/Dvorak_American_Suite.mp3'),
(15, 'Dvorak_Serenade_For_Strings.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Dvorak\\Dvorak_Serenade_For_Strings.mp3', '/home/pi/MP3s/Music/Dvorak/Dvorak_Serenade_For_Strings.mp3'),
(16, 'Dvorak_Symphony_1.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Dvorak\\Dvorak_Symphony_1.mp3', '/home/pi/MP3s/Music/Dvorak/Dvorak_Symphony_1.mp3'),
(17, 'Dvorak_Symphony_7.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Dvorak\\Dvorak_Symphony_7.mp3', '/home/pi/MP3s/Music/Dvorak/Dvorak_Symphony_7.mp3'),
(18, 'Dvorak_Symphony_9.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Dvorak\\Dvorak_Symphony_9.mp3', '/home/pi/MP3s/Music/Dvorak/Dvorak_Symphony_9.mp3'),
(19, 'Grechaninov_Symphony_1.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Grechaninov\\Grechaninov_Symphony_1.mp3', '/home/pi/MP3s/Music/Grechaninov/Grechaninov_Symphony_1.mp3'),
(20, 'Grechaninov_Symphony_5.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Grechaninov\\Grechaninov_Symphony_5.mp3', '/home/pi/MP3s/Music/Grechaninov/Grechaninov_Symphony_5.mp3'),
(21, 'Holst_The_Planets.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Holst\\Holst_The_Planets.mp3', '/home/pi/MP3s/Music/Holst/Holst_The_Planets.mp3'),
(22, 'Kalinnikov_Serenade_For_Strings.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Kalinnikov\\Kalinnikov_Serenade_For_Strings.mp3', '/home/pi/MP3s/Music/Kalinnikov/Kalinnikov_Serenade_For_Strings.mp3'),
(23, 'Kalinnikov_Suite.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Kalinnikov\\Kalinnikov_Suite.mp3', '/home/pi/MP3s/Music/Kalinnikov/Kalinnikov_Suite.mp3'),
(24, 'Kalinnikov_Symphony_1.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Kalinnikov\\Kalinnikov_Symphony_1.mp3', '/home/pi/MP3s/Music/Kalinnikov/Kalinnikov_Symphony_1.mp3'),
(25, 'Kalinnikov_Symphony_2.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Kalinnikov\\Kalinnikov_Symphony_2.mp3', '/home/pi/MP3s/Music/Kalinnikov/Kalinnikov_Symphony_2.mp3'),
(26, 'Kalinnikov_Tsar_Boris.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Kalinnikov\\Kalinnikov_Tsar_Boris.mp3', '/home/pi/MP3s/Music/Kalinnikov/Kalinnikov_Tsar_Boris.mp3'),
(27, 'Kalinnikov_Two_Intermezzi_For_Orchestra.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Kalinnikov\\Kalinnikov_Two_Intermezzi_For_Orchestra.mp3', '/home/pi/MP3s/Music/Kalinnikov/Kalinnikov_Two_Intermezzi_For_Orchestra.mp3'),
(28, 'Mussorgsky_Pictures_At_An_Exhibition.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Mussorgsky\\Mussorgsky_Pictures_At_An_Exhibition.mp3', '/home/pi/MP3s/Music/Mussorgsky/Mussorgsky_Pictures_At_An_Exhibition.mp3'),
(29, 'Rimsky-Korsakov_Antar.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Rimsky-Korsakov\\Rimsky-Korsakov_Antar.mp3', '/home/pi/MP3s/Music/Rimsky-Korsakov/Rimsky-Korsakov_Antar.mp3'),
(30, 'Rimsky-Korsakov_Scheherazade.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Rimsky-Korsakov\\Rimsky-Korsakov_Scheherazade.mp3', '/home/pi/MP3s/Music/Rimsky-Korsakov/Rimsky-Korsakov_Scheherazade.mp3'),
(31, 'Rimsky-Korsakov_Symphony_1.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Rimsky-Korsakov\\Rimsky-Korsakov_Symphony_1.mp3', '/home/pi/MP3s/Music/Rimsky-Korsakov/Rimsky-Korsakov_Symphony_1.mp3'),
(32, 'Rimsky-Korsakov_Symphony_3.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Rimsky-Korsakov\\Rimsky-Korsakov_Symphony_3.mp3', '/home/pi/MP3s/Music/Rimsky-Korsakov/Rimsky-Korsakov_Symphony_3.mp3'),
(33, 'Rimsky-Korsakov_The_Legend_of_the_Invisible_City_of_Kitezh_Opera.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Rimsky-Korsakov\\Rimsky-Korsakov_The_Legend_of_the_Invisible_City_of_Kitezh_Opera.mp3', '/home/pi/MP3s/Music/Rimsky-Korsakov/Rimsky-Korsakov_The_Legend_of_the_Invisible_City_of_Kitezh_Opera.mp3'),
(34, 'Rimsky-Korsakov_The_Legend_of_the_Invisible_City_of_Kitezh_Suite.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Rimsky-Korsakov\\Rimsky-Korsakov_The_Legend_of_the_Invisible_City_of_Kitezh_Suite.mp3', '/home/pi/MP3s/Music/Rimsky-Korsakov/Rimsky-Korsakov_The_Legend_of_the_Invisible_City_of_Kitezh_Suite.mp3'),
(35, 'Vivaldi_Four_Seasons.mp3', 1, 'C:\\pi\\home\\pi\\MP3s\\Music\\Vivaldi\\Vivaldi_Four_Seasons.mp3', '/home/pi/MP3s/Music/Vivaldi/Vivaldi_Four_Seasons.mp3'),
(36, 'Windows_NT_5_Shutdown_Sound.mp3', 1, 'C:\\pi\\home\\pi\\Windows\\Windows_NT_5_Shutdown_Sound.mp3', '/home/pi/Windows/Windows_NT_5_Shutdown_Sound.mp3'),
(37, 'Windows_NT_5_Startup_Sound.mp3', 1, 'C:\\pi\\home\\pi\\Windows\\Windows_NT_5_Startup_Sound.mp3', '/home/pi/Windows/Windows_NT_5_Startup_Sound.mp3');


ALTER TABLE `file_locations`
  ADD PRIMARY KEY (`file_num`),
  ADD UNIQUE KEY `file_name` (`file_name`),
  ADD UNIQUE KEY `server_dir` (`server_dir`),
  ADD UNIQUE KEY `pi_dir` (`pi_dir`);


ALTER TABLE `file_locations`
  MODIFY `file_num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
SET FOREIGN_KEY_CHECKS=1;