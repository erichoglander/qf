/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {

	config.toolbarGroups = [
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'styles', 		 groups: [ 'formats' ] },
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

	config.removeButtons = 'Subscript,Superscript,Anchor,Blockquote,Indent,Outdent,SpecialChar,HorizontalRule,Styles';
	
	config.format_tags = 'p';
	
	config.filebrowserUploadUrl = "/file/ajax/ckeditor";
	config.filebrowserBrowseUrl = "/file/browser/ckeditor";
	config.filebrowserImageUploadUrl = "/file/ajax/ckeditor?type=image";
	config.filebrowserImageBrowseUrl = "/file/browser/ckeditor?type=image";
	config.filebrowserWindowWidth = "880";
	config.filebrowserWindowHeight = "640";
	
	config.height = 400;
	
	config.disallowedContent = "img{width, height}[width, height]";
	
	config.qtWidth = "100%";
	
	config.language = "sv";
	
};
