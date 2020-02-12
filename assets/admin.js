window.CYB = window.CYB || {};

window.CYB.AdminPage = function () {
    let $ = jQuery;

    this.initialize = function () {
        bindForm($('.cyb-core-admin form.analytics-google'));
        bindForm($('.cyb-core-admin form.analytics-matomo'));
    };

    let bindForm = function ($form) {
        if ($form.length > 0) {
            $form.on('submit', function () {
                let $submitButton = $(this).find('button[type=submit]');
                setButtonRunning($submitButton, true);
                $submitButton.removeClass('btn-danger');
                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    data: getFormData($(this)),
                    dataType: 'json'
                }).then(function(data, textStatus, jqXHR) {
                    if (data.hasOwnProperty('success') && data.success === true) {
                        if (data.hasOwnProperty('data')) {
                            setFormData($form, data.data);
                        }
                        setButtonRunning($submitButton, false);
                        buttonFlashSuccess($submitButton, true);
                    } else {
                        setButtonRunning($submitButton, false);
                        buttonFlashSuccess($submitButton, false);
                        console.error(textStatus, jqXHR);
                    }
                }, function (jqXHR, textStatus, errorThrown) {
                    setButtonRunning($submitButton, false);
                    buttonFlashSuccess($submitButton, false);
                    console.error(textStatus, errorThrown, jqXHR);
                });
                return false;
            });
        }
    };

    let setButtonRunning = function ($button, isRunning) {
        if ($button.length > 0) {
            if (isRunning) {
                $button.addClass('running');
            } else {
                $button.removeClass('running');
            }
            $button.prop('disabled', isRunning);
        }
    };

    let buttonFlashSuccess = function ($button, isSuccess) {
        if ($button.length > 0) {
            let className = (isSuccess ? 'btn-success' : 'btn-danger');
            $button.addClass(className);
            window.setTimeout(function () {
                $button.removeClass(className);
            }, 1000);
        }
    };

    let getFormData = function ($form) {
        let formData = {};
        $.map($form.serializeArray(), function(obj, index) {
            formData[obj['name']] = obj['value'];
        });
        return formData;
    };

    let setFormData = function ($form, data) {
        let keys = Object.keys(data);
        for (let key of keys) {
            let $field = $form.find('[name=' + key + ']');
            if ($field.length > 0) {
                let type = $field.prop('type');
                if (type === 'checkbox') {
                    $field.prop('checked', (data[key] ? true : false));
                } else if (type === 'text' || type === 'textarea') {
                    $field.val(data[key]);
                }
            }
        }
    }
};

jQuery(function($) {
    let cybAdminPage = new window.CYB.AdminPage();
    cybAdminPage.initialize();
});
