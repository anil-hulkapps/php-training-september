
export default {
    exportIcon() {
        return '<svg class="svg" viewBox="0 0 20 20" class="v3ASA" focusable="false" aria-hidden="true"><path d="M13.707 6.707a.997.997 0 0 1-1.414 0L11 5.414V13a1 1 0 1 1-2 0V5.414L7.707 6.707a.999.999 0 1 1-1.414-1.414l3-3a.999.999 0 0 1 1.414 0l3 3a.999.999 0 0 1 0 1.414zM17 18H3a1 1 0 1 1 0-2h14a1 1 0 1 1 0 2z"></path></svg>'

    },
    importIcon() {
        return '<svg class="svg" viewBox="0 0 20 20" class="v3ASA" focusable="false" aria-hidden="true"><path d="M9.293 13.707l-3-3a.999.999 0 1 1 1.414-1.414L9 10.586V3a1 1 0 1 1 2 0v7.586l1.293-1.293a.999.999 0 1 1 1.414 1.414l-3 3a.999.999 0 0 1-1.414 0zM17 16a1 1 0 1 1 0 2H3a1 1 0 1 1 0-2h14z"></path></svg>';

    },
    setDateFormat() {
        return 'MM-DD-YYYY';
    },
    currentDateTime() {
        return moment().format('MM-DD-YYYY');
    },
}
