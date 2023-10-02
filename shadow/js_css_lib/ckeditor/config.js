/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
    // console.log(CKEDITOR.config.disallowedContent)
    // CKEDITOR.filter.disallow( 'img{width,height}' );

    //region Запрет для адаптива изображения должны быть без фиксированного размера
    config.disallowedContent = 'img[width,height]{width,height}';
    //endregion
    config.coreStyles_italic = { element : 'i', overrides : 'em' };//замена em на i
    config.skin = 'bootstrapck';
    config.removePlugins = 'save,newpage,preview,print';
    config.language = 'ru';
    config.filebrowserImageBrowseUrl = CKEDITOR.basePath+ 'filemanager/dialog.php?type=1&editor=ckeditor&fldr=';
    config.filebrowserUploadUrl  = CKEDITOR.basePath+ 'filemanager/dialog.php?type=2&editor=ckeditor&fldr=';
    config.filebrowserBrowseUrl      = CKEDITOR.basePath+ 'filemanager/dialog.php?type=2&editor=ckeditor&fldr=';
};
