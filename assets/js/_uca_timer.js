
/* Gestion des timers javascript */
_uca.timer = {}

_uca.timer.value = null;
_uca.timer.htmlId = null;

_uca.timer.h = function () {
    let t = _uca.timer.value;
    return (t.getDate() - 1) * 24 + t.getHours() + _uca.timer.value.getTimezoneOffset() / 60;
}

_uca.timer.m = function () {
    let t = _uca.timer.value;
    return ('0' + t.getMinutes()).slice(-2);
}

_uca.timer.s = function () {
    let t = _uca.timer.value;
    return ('0' + t.getSeconds()).slice(-2);
}

_uca.timer.init = function (htmlId, t) {
    _uca.timer.htmlId = htmlId;
    _uca.timer.phpValue = t;
    let val = 'expired';
    if (t == null) {
        $('#' + _uca.timer.htmlId).html(val);
    }
    else {
        _uca.timer.value = new Date((((t.d * 24 + t.h) * 60 + t.i) * 60 + (t.s + t.f)) * 1000);
        setInterval(() => {
            _uca.timer.value = new Date(_uca.timer.value.getTime() - 1000);
            if (_uca.timer.value.getTime() >= 0) {
                val = _uca.timer.h() + ':' + _uca.timer.m() + ':' + _uca.timer.s()
            }
            else {
                val = 'expired';
            }
            $('#' + _uca.timer.htmlId).html(val);
        }, 1000);
    }
}