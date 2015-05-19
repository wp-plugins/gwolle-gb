// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2011 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// Html tags
// http://en.wikipedia.org/wiki/html
// ----------------------------------------------------------------------------
// Basic set. Feel free to add more tags
// ----------------------------------------------------------------------------
var marktitup_mySettings = {
	onTab: { keepDefault:false, replaceWith:'    ' },
	markupSet:  [
		{name:'Bold', key:'B', openWith:'(!([b]|!|<b>)!)', closeWith:'(!([/b]|!|</b>)!)' },
		{name:'Italic', key:'I', openWith:'(!([i]|!|<i>)!)', closeWith:'(!([/i]|!|</i>)!)'  },
		{separator:'---------------' },
		{name:'Bulleted List', openWith:'    [li]', closeWith:'[/li]', multiline:true, openBlockWith:'[ul]\n', closeBlockWith:'\n[/ul]'},
		{name:'Numeric List', openWith:'    [li]', closeWith:'[/li]', multiline:true, openBlockWith:'[ol]\n', closeBlockWith:'\n[/ol]'},
		{separator:'---------------' },
		{name:'Picture', key:'P', replaceWith:'[img][![Source:!:http://]!][/img]' },
		{name:'Link', key:'L', openWith:'[url href=[![Link:!:http://]!]]', closeWith:'[/url]', placeHolder:'Your text to link...' },
		{separator:'---------------' },
		{name:'Clean', className:'clean', replaceWith:function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, "") } }
	]
}


jQuery(document).ready(function() {
	jQuery('#gwolle_gb_content').markItUp(marktitup_mySettings);
});
