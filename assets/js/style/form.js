import './w3color';

function replaceFloat(f) {
    return parseFloat(f.replace(',','.')) / 100.00;;
}

function createColor(base, variation)
{
    let color = w3color($(`#ucabundle_style_${base}Color`).val());
    color.lightness += replaceFloat($(`#ucabundle_style_${base}${variation}`).val());
    return color;
}

function updateColor(base, variation)
{
    $(`#${base}${variation} > div.js-bg`)
        .css('background-color', createColor(base, variation).toHslString())
    ;
    $(`#${base}${variation} span.js-rgb-hex`).text(w3color($(`#${base}${variation} > div.js-bg`).css('background-color')).toHexString());
}

function updateRGBHex(base)
{
    $(`#${base}Color span.js-rgb-hex`).text($(`#ucabundle_style_${base}Color`).val());
}

function updateBG(from, to)
{
    $(`#${to}Color div.js-bg`)
        .css('background-color', $(`#${from}Color span.js-rgb-hex`).text())
    ;
}

function updateFG(from, to)
{
    $(`#${to}Color span.js-fg`)
        .css('color', $(`#${from}Color span.js-rgb-hex`).text())
    ;
}

function changePrimaryColors() {
    updateColor('primary', 'Hover');
    updateColor('primary', 'Shadow');
}

function changeSecondaryColors() {
    updateColor('secondary', 'Hover');
    updateColor('secondary', 'Shadow');
}

function changeSuccessColors() {
    updateColor('success', 'Hover');
    updateColor('success', 'Shadow');
}

function changeWarningColors() {
    updateColor('warning', 'Hover');
    updateColor('warning', 'Shadow');
}

function changeDangerColors() {
    updateColor('danger', 'Hover');
    updateColor('danger', 'Shadow');
}


function changeNavbarColors() {
    updateRGBHex('navbarBackground');
    updateRGBHex('navbarForeground');
    updateBG('navbarBackground','navbarForeground');
    updateFG('navbarForeground','navbarForeground');
    updateBG('navbarBackground','navbarBackground');
    updateFG('navbarForeground','navbarBackground');
}

function changeColors() {
    changeDangerColors();
    changeSuccessColors();
    changeWarningColors();
    changeSecondaryColors();
    changePrimaryColors();
    changeNavbarColors();
    updateRGBHex('primary');
    updateRGBHex('secondary');
    updateRGBHex('success');
    updateRGBHex('warning');
    updateRGBHex('danger');
}

changeColors();

$('#ucabundle_style_primaryColor, #ucabundle_style_primaryHover, #ucabundle_style_primaryShadow').on('change', () => {
    updateRGBHex('primary');
    changePrimaryColors();
});

$('#ucabundle_style_primaryHover, #ucabundle_style_primaryShadow').on('input', () => {
    changePrimaryColors();
});

$('#ucabundle_style_secondaryColor, #ucabundle_style_secondaryHover, #ucabundle_style_secondaryShadow').on('change', () => {
    updateRGBHex('secondary');
    changeSecondaryColors();
});

$('#ucabundle_style_secondaryHover, #ucabundle_style_secondaryShadow').on('input', () => {
    changeSecondaryColors();
});


$('#ucabundle_style_navbarBackgroundColor, #ucabundle_style_navbarForegroundColor').on('change', () => {
    changeNavbarColors();
});

$('#ucabundle_style_successColor, #ucabundle_style_successHover, #ucabundle_style_successShadow').on('change', () => {
    updateRGBHex('success');
    changeSuccessColors();
});

$('#ucabundle_style_successHover, #ucabundle_style_successShadow').on('input', () => {
    changeSuccessColors();
});

$('#ucabundle_style_warningColor, #ucabundle_style_warningHover, #ucabundle_style_warningShadow').on('change', () => {
    updateRGBHex('warning');
    changeWarningColors();
});

$('#ucabundle_style_warningHover, #ucabundle_style_warningShadow').on('input', () => {
    changeWarningColors();
});

$('#ucabundle_style_dangerColor, #ucabundle_style_dangerHover, #ucabundle_style_dangerShadow').on('change', () => {
    updateRGBHex('danger');
    changeDangerColors();
});

$('#ucabundle_style_dangerHover, #ucabundle_style_dangerShadow').on('input', () => {
    changeDangerColors();
});
