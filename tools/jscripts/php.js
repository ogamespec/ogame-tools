
function php_number_format( number, decimals, dec_point, thousands_sep ) {
    // http://kevin.vanzonneveld.net
    var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
    var d = dec_point == undefined ? ',' : dec_point;
    var t = thousands_sep == undefined ? '.' : thousands_sep, s = n < 0 ? '-' : '';
    var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '', j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : '');
}

function php_strpos( haystack, needle, offset){
    // http://kevin.vanzonneveld.net
    var i = (haystack+'').indexOf( needle, offset ); 
    return i===-1 ? false : i;
}

function php_substr( f_string, f_start, f_length ) {
    // http://kevin.vanzonneveld.net
    f_string += '';
    if(f_start < 0) {
        f_start += f_string.length;
    }
    if(f_length == undefined) {
        f_length = f_string.length;
    } else if(f_length < 0){
        f_length += f_string.length;
    } else {
        f_length += f_start;
    }
    if(f_length < f_start) {
        f_length = f_start;
    }
    return f_string.substring(f_start, f_length);
}

function php_str_replace(search, replace, subject) {
    // http://kevin.vanzonneveld.net
    var s = subject;
    var ra = r instanceof Array, sa = s instanceof Array;
    var f = [].concat(search);
    var r = [].concat(replace);
    var i = (s = [].concat(s)).length;
    var j = 0;
    while (j = 0, i--) {
        if (s[i]) {
            while (s[i] = (s[i]+'').split(f[j]).join(ra ? r[j] || '' : r[0]), ++j in f){};
        }
    }
    return sa ? s : s[0];
}
