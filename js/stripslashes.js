/**
 * stripslashes
 * Does what the PHP function 'stripslashes()' does.
 * Thanks to http://javascript.about.com/library/bladdslash.htm!
 */
function stripslashes(str) {
  str=str.replace(/\\'/g,'\'');
  str=str.replace(/\\"/g,'"');
  str=str.replace(/\\0/g,'\0');
  str=str.replace(/\\\\/g,'\\');
  return str;
}