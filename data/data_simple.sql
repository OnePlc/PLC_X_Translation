--
-- Extra Form Fields
--
INSERT INTO `core_form_field` (`Field_ID`, `type`, `label`, `fieldkey`, `tab`, `form`, `class`, `url_view`, `url_list`, `show_widget_left`, `allow_clear`, `readonly`, `tbl_cached_name`, `tbl_class`, `tbl_permission`) VALUES
(NULL, 'text', 'Translation', 'translation', 'translation-base', 'translation-single', 'col-md-3', '/translation/view/##ID##', '', '0', '1', '0', '', '', ''),
(NULL, 'select', 'Language', 'language_idfs', 'translation-base', 'translation-single', 'col-md-2', '', '/tag/api/list/translation-single/category', '0', '1', '0', 'entitytag-single', 'OnePlace\\Tag\\Model\\EntityTagTable', 'add-OnePlace\\Tag\\Controller\\StateController');

--
-- Extra Permissions
--
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`) VALUES
('generate', 'OnePlace\\Translation\\Controller\\TranslationController', 'Generate', '', '', '0');

