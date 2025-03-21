function errorAlert(Message) {
    Swal.fire({
        html: Message,
        icon: "error",
        buttonsStyling: false,
        confirmButtonText: "Ok",
        // focusConfirm: true,
        didOpen: (popup) => {
            const okButton = popup.querySelector('.swal2-confirm');
            if (okButton) {
                okButton.focus();
            }
        },
        customClass: {
            confirmButton: "btn btn-danger"
        }
    });
}

function successAlert(Message) {
    Swal.fire({
        html: Message,
        icon: "success",
        buttonsStyling: false,
        confirmButtonText: "Ok",
        // focusConfirm: true,
        customClass: {
            confirmButton: "btn btn-primary"
        },
        didOpen: (popup) => {
            const okButton = popup.querySelector('.swal2-confirm');
            if (okButton) {
                okButton.focus();
            }
        },
    });
}

function warningAlert(Message) {
    Swal.fire({
        text: Message,
        icon: "warning",
        buttonsStyling: false,
        confirmButtonText: "Ok",
        // focusConfirm: true,
        didOpen: (popup) => {
            const okButton = popup.querySelector('.swal2-confirm');
            if (okButton) {
                okButton.focus();
            }
        },
        customClass: {
            confirmButton: "btn btn-warning"
        }
    });
}

function infoAlert(Message = null) {
    Swal.fire({
        text: Message,
        icon: "info",
        buttonsStyling: false,
        confirmButtonText: "Ok",
        // focusConfirm: true,
        didOpen: (popup) => {
            const okButton = popup.querySelector('.swal2-confirm');
            if (okButton) {
                okButton.focus();
            }
        },
        customClass: {
            confirmButton: "btn btn-info"
        }
    });
}

function loadingAlert(Message = null) {
    // const htmlClass = document.documentElement.classList.contains('dark-style') ? 'dark' : 'light';
    // const fillColor = htmlClass === 'dark' ? '#fff' : '#000';
    const fillColor = '#fff';
    const svgIcon = `
        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><style>.spinner_OSmW{fill: ${fillColor}; transform-origin:center;animation:spinner_T6mA .75s step-end infinite}@keyframes spinner_T6mA{8.3%{transform:rotate(30deg)}16.6%{transform:rotate(60deg)}25%{transform:rotate(90deg)}33.3%{transform:rotate(120deg)}41.6%{transform:rotate(150deg)}50%{transform:rotate(180deg)}58.3%{transform:rotate(210deg)}66.6%{transform:rotate(240deg)}75%{transform:rotate(270deg)}83.3%{transform:rotate(300deg)}91.6%{transform:rotate(330deg)}100%{transform:rotate(360deg)}}</style><g class="spinner_OSmW"><rect x="11" y="1" width="2" height="5" opacity=".14"/><rect x="11" y="1" width="2" height="5" transform="rotate(30 12 12)" opacity=".29"/><rect x="11" y="1" width="2" height="5" transform="rotate(60 12 12)" opacity=".43"/><rect x="11" y="1" width="2" height="5" transform="rotate(90 12 12)" opacity=".57"/><rect x="11" y="1" width="2" height="5" transform="rotate(120 12 12)" opacity=".71"/><rect x="11" y="1" width="2" height="5" transform="rotate(150 12 12)" opacity=".86"/><rect x="11" y="1" width="2" height="5" transform="rotate(180 12 12)"/></g></svg>
    `;

    let options = {
        imageUrl: 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgIcon),
        imageWidth: 100,
        imageHeight: 100,
        // height: 100,
        // width: 400,
        imageAlt: 'Custom SVG Icon',
        showConfirmButton: false,
        allowOutsideClick: false,
        customClass: {
            container: 'transparent-swal2'
        },
    };

    Message ? options['html'] = '<span style="color: #fff;">'+Message+'</span>' : '';
    Swal.fire(options);
}

function toastSuccess(message, title) {
    toastr.success(message, title, {
        positionClass: 'toast-top-right',
        closeButton: true,
        timeOut: 5000,
        extendedTimeOut: 1000,
        progressBar: true,
        tapToDismiss: false
    });
}

function toastWarning(message, title) {
    toastr.warning(message, title, {
        positionClass: 'toast-top-right',
        closeButton: true,
        timeOut: 5000,
        extendedTimeOut: 1000,
        progressBar: true,
        tapToDismiss: false
    });
}

function toastError(message, title) {
    toastr.error(message, title, {
        positionClass: 'toast-top-right',
        closeButton: true,
        timeOut: 5000,
        extendedTimeOut: 1000,
        progressBar: true,
        tapToDismiss: false
    });
}

function toastInfo(message, title) {
    toastr.info(message, title, {
        positionClass: 'toast-top-right',
        closeButton: true,
        timeOut: 5000,
        extendedTimeOut: 1000,
        progressBar: true,
        tapToDismiss: false
    });
}


//blockui

function blockPage() {
    $.blockUI({
        message: '<div class="sk-circle mx-auto">\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                    <div class="sk-circle-dot"></div>\n' +
            '                  </div>',
        css: {
            backgroundColor: 'transparent',
            border: '0'
        },
        overlayCSS: {
            backgroundColor: 'rgba(0, 0, 0, 0.6)',
            opacity: 1,
            backdropFilter: 'blur(4px)',
            '-webkit-backdrop-filter': 'blur(4px)'
        }
    });
}

function unblockPage() {
    $(document).ajaxStop($.unblockUI);
}
