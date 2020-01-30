--
-- Base Table
--
CREATE TABLE `translation` (
  `Translation_ID` int(11) NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `translation`
  ADD PRIMARY KEY (`Translation_ID`);

ALTER TABLE `translation`
  MODIFY `Translation_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Permissions
--
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`) VALUES
('add', 'OnePlace\\Translation\\Controller\\TranslationController', 'Add', '', '', 0),
('edit', 'OnePlace\\Translation\\Controller\\TranslationController', 'Edit', '', '', 0),
('index', 'OnePlace\\Translation\\Controller\\TranslationController', 'Index', 'Translations', '/translation', 1),
('list', 'OnePlace\\Translation\\Controller\\ApiController', 'List', '', '', 1),
('view', 'OnePlace\\Translation\\Controller\\TranslationController', 'View', '', '', 0);

--
-- Form
--
INSERT INTO `core_form` (`form_key`, `label`, `entity_class`, `entity_tbl_class`) VALUES
('translation-single', 'Translation', 'OnePlace\\Translation\\Model\\Translation', 'OnePlace\\Translation\\Model\\TranslationTable');

--
-- Index List
--
INSERT INTO `core_index_table` (`table_name`, `form`, `label`) VALUES
('translation-index', 'translation-single', 'Translation Index');

--
-- Tabs
--
INSERT INTO `core_form_tab` (`Tab_ID`, `form`, `title`, `subtitle`, `icon`, `counter`, `sort_id`, `filter_check`, `filter_value`) VALUES ('translation-base', 'translation-single', 'Translation', 'Base', 'fas fa-cogs', '', '0', '', '');

--
-- Buttons
--
INSERT INTO `core_form_button` (`Button_ID`, `label`, `icon`, `title`, `href`, `class`, `append`, `form`, `mode`, `filter_check`, `filter_value`) VALUES
(NULL, 'Save Translation', 'fas fa-save', 'Save Translation', '#', 'primary saveForm', '', 'translation-single', 'link', '', ''),
(NULL, 'Edit Translation', 'fas fa-edit', 'Edit Translation', '/translation/edit/##ID##', 'primary', '', 'translation-view', 'link', '', ''),
(NULL, 'Add Translation', 'fas fa-plus', 'Add Translation', '/translation/add', 'primary', '', 'translation-index', 'link', '', '');

--
-- Fields
--
INSERT INTO `core_form_field` (`Field_ID`, `type`, `label`, `fieldkey`, `tab`, `form`, `class`, `url_view`, `url_ist`, `show_widget_left`, `allow_clear`, `readonly`, `tbl_cached_name`, `tbl_class`, `tbl_permission`) VALUES
(NULL, 'text', 'Name', 'label', 'translation-base', 'translation-single', 'col-md-3', '/translation/view/##ID##', '', 0, 1, 0, '', '', '');

COMMIT;