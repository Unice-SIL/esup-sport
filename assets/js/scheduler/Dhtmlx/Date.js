var dateToStr = scheduler.date.date_to_str("%Y-%m-%d %H:%i");

function show_minical() {
    if (scheduler.isCalendarVisible()) {
        scheduler.destroyCalendar();
    } else {
        scheduler.renderCalendar({
            position: "dhx_minical_icon",
            date: scheduler._date,
            navigation: true,
            handler: function (date, calendar) {
                scheduler.setCurrentView(date);
                scheduler.destroyCalendar()
            }
        });
    }
}
function show_end_date_minical() {
    if (scheduler.isCalendarVisible()) {
        scheduler.destroyCalendar();
    } else {
        scheduler.renderCalendar({
            position: "dhx_end_date_minical_icon",
            date: scheduler._date,
            navigation: true,
            handler: function (date, calendar) {
                let day = date.getDate();
                let month = date.getMonth() + 1;
                let year = date.getFullYear();
                if (day < 10) {
                    day = '0' + day;
                }
                if (month < 10) {
                    month = '0' + month;
                }
                document.getElementById("dhx_end_date_minical_icon").value = [day, month, year].join('-');
                scheduler.config.repeat_date_of_end = [day, month, year].join('/');
                scheduler.destroyCalendar();
            }
        });
    }
}
function show_extend_date_minical() {
    if (scheduler.isCalendarVisible()) {
        scheduler.destroyCalendar();
    } else {
        scheduler.renderCalendar({
            position: "dhx_extend_date",
            date: scheduler._date,
            navigation: true,
            handler: function (date, calendar) {
                let day = date.getDate();
                let month = date.getMonth() + 1;
                let year = date.getFullYear();
                if (day < 10) {
                    day = '0' + day;
                }
                if (month < 10) {
                    month = '0' + month;
                }
                document.getElementById("dhx_extend_date").value = [day, month, year].join('-');
                scheduler.config.repeat_date_of_end = [day, month, year].join('/');
                scheduler.destroyCalendar();
            }
        });
    }
}

export {dateToStr}
export {show_minical}
export {show_end_date_minical}
export {show_extend_date_minical}