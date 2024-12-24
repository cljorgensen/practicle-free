-- 3.93.117 -> 3.93.121
-- Date: 2024-12-12
-- Changed RoleID to GroupID in itsm_modules
ALTER TABLE itsm_modules
CHANGE COLUMN RoleID GroupID MEDIUMINT DEFAULT NULL;

-- Set GroupID to NULL for all modules to avoid conflicts
--UPDATE `itsm_modules` SET GroupID = NULL;

-- 3.93.121 -> 3.93.122
ALTER TABLE `logsystem` CHANGE `ID` `ID` BIGINT NOT NULL AUTO_INCREMENT;

-- 3.93.122 -> 3.93.123
INSERT INTO `settings` (`ID`, `SettingsTypeID`, `SettingName`, `SettingDescription`, `SettingValue`, `Active`) VALUES ('65', '2', 'Max file size', 'Max file size for uploads in mb', '300', '1');
