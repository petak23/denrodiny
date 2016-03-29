/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
 CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here. For example:
    config.language = 'sk';
    // config.uiColor = '#AADC6E';

    config.toolbar_OznamToolbar =
    [
      ['Source', '-', 'SpellChecker', '-', 'Link','Unlink','Anchor'],
      ['Image','Table','Smiley','SpecialChar'],
      '/',
      ['Format'],
      ['Bold','Italic','Underline','Strike','-','Subscript','Superscript','-','NumberedList','BulletedList'],
      ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
      ['TextColor','BGColor','Zalomenie'/*,'Registrovany','anigraph_reg1'*/]
    ];
    config.toolbar_OznamToolbar_Spravca =
    [
      ['Source', '-', 'SpellChecker', '-', 'Link','Unlink'], ['Image','Table','Smiley','SpecialChar'],
			['TextColor','BGColor','Zalomenie'],
      '/',
      ['Format'],
      ['Bold','Italic','Underline','Strike','-','Subscript','Superscript','-','NumberedList','BulletedList']
    ];
    config.format_tags = 'p;h3;h4';/*;div*/
    /*config.format_div = { element : 'div', attributes : { 'class' : 'oznam' } };*/
    config.skin = 'kama';
  };


