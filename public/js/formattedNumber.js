$(document).on('keypress', '.formattedNumber', function (e) {
    const charCode = e.which ? e.which : e.keyCode;
    if (charCode < 48 || charCode > 57) {
        e.preventDefault();
    }
})

$(document).on('input', '.formattedNumber', function (e) {
    const formattedValue = $(this).val();
    const parsedNumber = parseInt(formattedValue.replace(/\./g, ''));

    if (!isNaN(parsedNumber)) {
        const formattedString = parsedNumber.toLocaleString('id-ID');
        $(this).val(formattedString);
    } else {

    }
});
