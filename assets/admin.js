window.CYB = window.CYB || {};

class CybCoreAdminPage {
    constructor() {
        this.initializeTabs();
        this.bindToggleSwitch();
        this.bindForm(jQuery('.cyb-core-admin form.xhr'));
    }

    initializeTabs() {
        let tabClassActive = 'nav-tab-active';
        jQuery('.cyb-core-admin .nav-tab-wrapper').each(function (i, obj) {
            jQuery(this).find('.nav-tab').each(function (i, tab) {
                let $tab = jQuery(tab);
                if (!$tab.hasClass(tabClassActive)) {
                    jQuery($tab.attr('href')).hide();
                }

                $tab.on('click', function (e) {
                    e.preventDefault();
                    let $currentTab = jQuery(this);

                    let $tabs = $currentTab.parent().find('.nav-tab');
                    $tabs.each(function (i, tab2) {
                        let $tab2 = jQuery(tab2);
                        $tab2.removeClass(tabClassActive);
                        jQuery($tab2.attr('href')).fadeOut(0);
                    });
                    $currentTab.addClass(tabClassActive);
                    jQuery($currentTab.attr('href')).fadeIn(500);
                });
            });
        });
    }

    bindToggleSwitch() {
        let instance = this;
        jQuery('.cyb-core-input-switch input').on('click', function () {

            let input = this;
            let $input = jQuery(this);
            let $container = $input.parent();
            instance.setClassRunning($container, true);

            let enabled = input.checked;
            let data = instance.getFormData($input.closest('form'));
            data = {
                ...data,
                enabled: enabled,
                key: $input.attr('name')
            };

            jQuery.ajax({
                method: 'POST',
                url: ajaxurl,
                data: data,
                dataType: 'json'
            }).then(function(data, textStatus, jqXHR) {
                if (data.hasOwnProperty('success') && data.success === true) {
                    instance.setClassRunning($container, false);
                    if (data.hasOwnProperty('data') && data.data.hasOwnProperty('enabled')) {
                        input.checked = data.data.enabled;
                    } else {
                        input.checked = enabled;
                    }
                } else {
                    instance.setClassRunning($container, false);
                    instance.setPropertyDisabled($input, true);
                    input.checked = !enabled;
                    console.error(textStatus, jqXHR);
                }
            }, function (jqXHR, textStatus, errorThrown) {
                instance.setClassRunning($container, false);
                instance.setPropertyDisabled($input, true);
                input.checked = !enabled;
                console.error(textStatus, errorThrown, jqXHR);
            });
        });
    }

    bindForm($form) {
        let instance = this;
        if ($form.length > 0) {
            $form.on('submit', function () {
                let $submitButton = jQuery(this).find('button[type=submit]');
                instance.setClassRunning($submitButton, true);
                instance.setPropertyDisabled($submitButton, true);
                $submitButton.removeClass('btn-danger');
                jQuery.ajax({
                    method: 'POST',
                    url: jQuery(this).attr('action'),
                    data: instance.getFormData(jQuery(this)),
                    dataType: 'json'
                }).then(function(data, textStatus, jqXHR) {
                    if (data.hasOwnProperty('success') && data.success === true) {
                        if (data.hasOwnProperty('data')) {
                            instance.setFormData($form, data.data);
                        }
                        instance.setClassRunning($submitButton, false);
                        instance.setPropertyDisabled($submitButton, false);
                        instance.buttonFlashSuccess($submitButton, true);
                    } else {
                        instance.setClassRunning($submitButton, false);
                        instance.setPropertyDisabled($submitButton, false);
                        instance.buttonFlashSuccess($submitButton, false);
                        console.error(textStatus, jqXHR);
                    }
                }, function (jqXHR, textStatus, errorThrown) {
                    instance.setClassRunning($submitButton, false);
                    instance.setPropertyDisabled($submitButton, false);
                    instance.buttonFlashSuccess($submitButton, false);
                    console.error(textStatus, errorThrown, jqXHR);
                });
                return false;
            });
        }
    }

    setClassRunning($element, isRunning) {
        if ($element.length > 0) {
            if (isRunning) {
                $element.addClass('running');
            } else {
                $element.removeClass('running');
            }
            $element.prop('disabled', isRunning);
        }
    }

    setPropertyDisabled($element, isRunning) {
        if ($element.length > 0) {
            $element.prop('disabled', isRunning);
        }
    }

    buttonFlashSuccess($button, isSuccess) {
        if ($button.length > 0) {
            let className = (isSuccess ? 'btn-success' : 'btn-danger');
            $button.addClass(className);
            window.setTimeout(function () {
                $button.removeClass(className);
            }, 1000);
        }
    }

    getFormData($form) {
        let formData = {};
        jQuery.map($form.serializeArray(), function(obj, index) {
            formData[obj['name']] = obj['value'];
        });
        return formData;
    }

    setFormData($form, data) {
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
}

jQuery(function ($) {
    new CybCoreAdminPage();
});
