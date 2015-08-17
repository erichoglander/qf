/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
	];

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Subscript,Superscript,Anchor,Blockquote,Indent,Outdent,SpecialChar,HorizontalRule';

	// Set the most common block elements.
	config.format_tags = 'p;div';
	
	config.filebrowserUploadUrl = "/file/ajax/ckeditor";
	config.filebrowserBrowseUrl = "/file/browser/ckeditor";
	config.filebrowserImageUploadUrl = "/file/ajax/ckeditor?type=image";
	config.filebrowserImageBrowseUrl = "/file/browser/ckeditor?type=image";
	config.filebrowserWindowWidth = "880";
	config.filebrowserWindowHeight = "640";
	
	config.height = 400;
	
	// config.disallowedContent = "img{width, height}[width, height]";
	
	config.qtWidth = "100%";
	
	config.language = "sv";
	// config.allowedContent = true;
	config.allowedContent = "img[!src,alt]{float};div{*};iframe[*]{*}";
	
};
