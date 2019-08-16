_uca = {
}

_uca.imgPreview = function (event) {
    let elemId = $(this).attr('id');
    let fileUrl = URL.createObjectURL(event.target.files[0]);
    $('#' + elemId + '_preview img:first').attr('src', fileUrl);
    $('#' + elemId + '_preview').removeClass('d-none');
}

_uca.toggleFormDisplay = function (ReferenceValues) {
    return function (event) {
        if ($(this).is(':checked')) {
            let val = $(this).val();
            let code = ReferenceValues[val];
            $(".form-group:has(." + code + "ToShow)").show();
            $(".form-group:has(." + code + "ToHide)").hide();
            $('.' + code + 'ToShow').prop('required', true);
            $('.' + code + 'ToHide').prop('required', false);
        }
    }
}

_uca.showEncadrants = _uca.toggleFormDisplay({ '0': 'nonEncadre', '1': 'encadre' });
_uca.showTarifs = _uca.toggleFormDisplay({ '0': 'nonPayant', '1': 'payant' });