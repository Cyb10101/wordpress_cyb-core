class CybCoreAdminPage {
    constructor() {
        this.initializeTabs();
        this.bindToggleSwitch();
        this.bindForms();
    }

    initializeTabs() {
        const instance = this;
        const tabClassActive = 'nav-tab-active';
        const tabs = document.querySelectorAll('.cyb-core-admin .nav-tab-wrapper .nav-tab');
        if (tabs.length > 0) {
            tabs.forEach(tab => {
                instance.toggleTabContainer(tab.hash, tab.classList.contains(tabClassActive));

                tab.addEventListener('click', (event) => {
                    event.preventDefault();

                    const tabs2 = event.target.parentNode.querySelectorAll('.nav-tab');
                    if (tabs2.length > 0) {
                        tabs2.forEach(tab2 => {
                            tab2.classList.remove(tabClassActive);

                            instance.toggleTabContainer(tab2.hash, false);
                        });

                        event.target.classList.add(tabClassActive);
                        instance.toggleTabContainer(event.target.hash, true);
                    }
                });
            });
        }
    }

    toggleTabContainer(id, visible) {
        const tabContainer = document.getElementById(id.replace(/^#/, ''));
        if (tabContainer) {
            tabContainer.style.display = visible ? 'block' : 'none';
        }
    }

    bindToggleSwitch() {
        const instance = this;
        const switches = document.querySelectorAll('.cyb-core-input-switch input');
        if (switches.length > 0) {
            switches.forEach(switchElement => {
                switchElement.addEventListener('click', (event) => {
                    const input = event.target;
                    instance.setClassRunning(input.parentNode, true);
                    const enabled = input.checked;

                    const form = input.closest('form');
                    let formData = (new FormData(form));
                    // let formDataJsonString = JSON.stringify(Object.fromEntries((new FormData(event.target)).entries()));
                    formData.set('enabled', enabled);
                    formData.set('key', input.getAttribute('name'));

                    fetch(form.getAttribute('action'), {
                        method: 'POST',
                        headers: {
                          'Accept': 'application/json',
                        //   'Content-Type': 'application/json'
                        },
                        body: formData
                    }).then(response => {
                        return response.json();
                    }).then(data => {
                        if (data.hasOwnProperty('success') && data.success === true) {
                            if (data.hasOwnProperty('data') && data.data.hasOwnProperty('enabled')) {
                                input.checked = data.data.enabled;
                            } else {
                                input.checked = enabled;
                            }
                            instance.setClassRunning(input.parentNode, false);
                        } else {
                            instance.setClassRunning(input.parentNode, true);
                            instance.setPropertyDisabled(input, true);
                            input.checked = !enabled;
                            console.error(`Fetch error!`);
                        }
                    }).catch(error => {
                        instance.setClassRunning(input.parentNode, true);
                        instance.setPropertyDisabled(input, true);
                        input.checked = !enabled;
                        console.error(`Fetch error: ${error.message}`);
                    });
                });
            });
        }
    }

    bindForms() {
        const instance = this;
        const forms = document.querySelectorAll('.cyb-core-admin form.xhr');
        if (forms.length > 0) {
            forms.forEach(form => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();

                    const submitButton = event.target.querySelector('button[type=submit]');
                    instance.setClassRunning(submitButton, true);
                    instance.setPropertyDisabled(submitButton, true);
                    submitButton.classList.remove('btn-danger');

                    const formData = (new FormData(event.target));
                    // const formDataJsonString = JSON.stringify(Object.fromEntries((new FormData(event.target)).entries()));

                    fetch(event.target.getAttribute('action'), {
                        method: 'POST',
                        headers: {
                          'Accept': 'application/json',
                        //   'Content-Type': 'application/json'
                        },
                        body: formData
                    }).then(response => {
                        return response.json();
                    }).then(data => {
                        if (data.hasOwnProperty('success') && data.success === true) {
                            if (data.hasOwnProperty('data')) {
                                instance.setFormData(event.target, data.data);
                            }
                            instance.setClassRunning(submitButton, false);
                            instance.setPropertyDisabled(submitButton, false);
                            instance.buttonFlashSuccess(submitButton, true);
                        } else {
                            instance.setClassRunning(submitButton, false);
                            instance.setPropertyDisabled(submitButton, false);
                            instance.buttonFlashSuccess(submitButton, false);
                            console.error(`Fetch error!`);
                        }
                    }).catch(error => {
                        instance.setClassRunning(submitButton, false);
                        instance.setPropertyDisabled(submitButton, false);
                        instance.buttonFlashSuccess(submitButton, false);
                        console.error(`Fetch error: ${error.message}`);
                    });
                    return false;
                });
            });
        }
    }

    setClassRunning(element, isRunning) {
        if (isRunning) {
            element.classList.add('running');
        } else {
            element.classList.remove('running');
        }
        element.disabled = isRunning;
    }

    setPropertyDisabled(element, isRunning) {
        element.disabled = isRunning;
    }

    buttonFlashSuccess(button, isSuccess) {
        const className = (isSuccess ? 'btn-success' : 'btn-danger');
        button.classList.add(className);
        window.setTimeout(() => {
            button.classList.remove(className);
        }, 2000);
    }

    setFormData(form, data) {
        for (const key of Object.keys(data)) {
            const field = form.querySelector('[name=' + key + ']');
            if (field) {
                const tagName = field.tagName.toLowerCase();
                const type = field.getAttribute('type');
                if (type && type === 'checkbox') {
                    field.checked = (data[key] ? true : false);
                } else if (type && ['text', 'textarea'].includes(type)) {
                    field.value = data[key];
                } else if (tagName && ['textarea'].includes(tagName)) {
                    field.value = data[key];
                }
            }
        }
    }
}

new CybCoreAdminPage();
