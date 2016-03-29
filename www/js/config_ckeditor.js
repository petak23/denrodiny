/*
Copyright (c) 04.01.2012, Ing. Peter VOJTECH ml. 
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	config.language = 'sk';
	// config.uiColor = '#AADC6E';
	// origin�l toolbaru je ulo�en� v ..\ckeditor\_source\plugins\toolbar\plugin.js
	//config.toolbar = 'MyToolbar';
	config.toolbar_AdminToolbar =
[
	['Source','-','Templates'],
	['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	'/',
	['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	['Link','Unlink','Anchor'],
	['Image','Table','HorizontalRule','Smiley','SpecialChar'],
	'/',
	['Format','-','TextColor','BGColor','ShowBlocks','Zalomenie','Registrovany','anigraph_reg1']
];
    config.toolbar_UserToolbar =
[
	['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	'/',
	['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	['Link','Unlink','Anchor'],
	['Image','Table','HorizontalRule','Smiley','SpecialChar'],
	'/',
	['Format','-','TextColor','BGColor','Zalomenie', 'Registrovany', 'About']
];
    config.toolbar_UserToolbarSmall =
[
	['Bold','Italic','Underline','Strike','-','Subscript','Superscript','-','NumberedList','BulletedList'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	['Link','Image','Table','HorizontalRule','Smiley','SpecialChar'],
	'/',
	['Format','-','TextColor','BGColor','-','Cut','Copy','Paste','PasteText','SpellChecker','-','Undo','Redo', 'Registrovany']
];
	config.toolbar_Todo =
[
	['Source','Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	['NumberedList','BulletedList','-','TextColor', 'Registrovany']
];
	config.toolbar_Oznam1Toolbar =
[
	['Source'/*],
	['Cut','Copy','Paste'*/, 'SpellChecker'],
	[/*'Undo','Redo','-','Find','Replace','-',*/'Link','Unlink'/*,'Anchor'*/],
  ['Image','Table',/*'HorizontalRule',*/'Smiley','SpecialChar'],
	'/',
  ['Format'],
	['Bold','Italic','Underline','Strike','-','Subscript','Superscript','-','NumberedList','BulletedList'],
	/*['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],*/
	['TextColor','BGColor','Zalomenie'/*,'Registrovany','anigraph_reg1'*/]
];
	config.format_tags = 'p;h2;h3;h4;div';
	config.format_div = { element : 'div', attributes : { 'class' : 'oznam' } };
	config.skin = 'kama';
};