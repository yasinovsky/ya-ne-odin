(function(exports) {



    /**
     * Представление фронтенда приложения
     * @type {ynoApplication}
     */
    const ynoApplication = (function() {

        /**
         * Конструктор
         * @constructor
         */
        function ynoApplication() {
            this._api_prefix = '/api';
        }

        /**
         * Выполняет запрос к api
         * @param {string} url Урл запроса
         * @param {Object} data Данные запроса
         * @param {Function} callback Обработчик обратного вызова
         */
        ynoApplication.prototype.api = function(url, data, callback) {
            $.ajax(this._api_prefix + url, { data: JSON.stringify(data), type: 'POST' })
                .done(function(response) { callback(response.result, response.error); });
        };

        /**
         * Обработчик главной страницы
         * @private
         */
        ynoApplication.prototype.main = function() {
            const self = this;
            const buttons = {
                read: $('[data-for="read-message"]'),
                write: $('[data-for="write-message"]'),
            };
            const elements = {
                lead: $('[data-for="lead-text"]'),
                main: $('[data-for="main-buttons"]'),
            };
            const leave = function() {
                elements.lead.hide(); // Скроем текст
                elements.main.hide(); // Скроем кнопки
            };
            // Немного поправим ширину кнопок для красоты :)
            Object.keys(buttons).forEach(function(key) {
                buttons[key].css('width', '250px');
            });
            buttons.read.bind('click', function() {
                const button = $(this); // Это шорткат
                button.attr('disabled', true);
                const view = $('[data-view="read"]');
                const controller = new SectionRead(self, view);
                controller.focus(function() {
                    button.removeAttr('disabled');
                    leave();
                });
            });
            buttons.write.bind('click', function() {
                const button = $(this); // Это шорткат
                button.attr('disabled', true);
                const view = $('[data-view="write"]');
                const controller = new SectionWrite(self, view);
                controller.focus(function() {
                    button.removeAttr('disabled');
                    leave();
                });
            });
        };

        /**
         * Возвращает шаблон Handlebars
         * @param {string} name Имя шаблона
         * @return {Function}
         */
        ynoApplication.prototype.hbTemplate = function(name) {
            return Handlebars.compile($('#' + name).html());
        };

        /**
         * Обработчик страницы авторизации
         */
        ynoApplication.prototype.signin = function() {
            new SignIn(this); // Весьма лаконично :)
        };

        /**
         * Обработчик ветки сообщений бекофиса
         * @param {String} uuid Идентификатор ветки
         */
        ynoApplication.prototype.thread = function(uuid) {
            new BackofficeThread(this, uuid);
        };

        return ynoApplication;

    })();



    /**
     * Представление формы авторизации
     * @type {SignIn}
     */
    const SignIn = (function() {

        /**
         * Конструктор
         * @param {ynoApplication} app Представление приложения
         * @param {HTMLElement} section Раздел
         * @constructor
         */
        function SignIn(app, section) {
            this._app = app; this._section = $('[data-view="sign-in"]');
            this._form = this._section.find('form');
            this._submit_form();
        }

        /**
         * Обрабатывает отправку формы
         * @private
         */
        SignIn.prototype._submit_form = function() {
            const self = this;
            const login = this._form.find('#login');
            const password = this._form.find('#password');
            const button = this._form.find('button[type="submit"]');
            this._form.submit(function(event) {
                event.preventDefault();
                button.attr('disabled', true);
                const request = { login: login.val(), password: password.val() };
                self._app.api('/signin', request, function(result, error) {
                    button.removeAttr('disabled');
                    switch (error) {
                        case null: window.location.href = result; break;
                        default: break;
                    }
                });
            });
        };

        return SignIn;

    })();



    /**
     * Представление ветки сообщений бекофиса
     * @type {BackofficeThread}
     */
    const BackofficeThread = (function() {

        /**
         * Конструктор
         * @param {ynoApplication} app Представление приложения
         * @param {String} uuid Идентификатор ветки
         * @constructor
         */
        function BackofficeThread(app, uuid) {
            this._app = app; this._uuid = uuid;
            this._form = $('form');
            this._submit_form();
        }

        /**
         * Обрабатывает отправку формы
         * @private
         */
        BackofficeThread.prototype._submit_form = function() {
            const self = this;
            const message = this._form.find('#message');
            const button = this._form.find('button[type="submit"]');
            this._form.submit(function(event) {
                event.preventDefault();
                button.attr('disabled', true);
                const request = { message: message.val(), thread: self._uuid, actor: true };
                self._app.api('/message/insert', request, function(result, error) {
                    button.removeAttr('disabled');
                    switch (error) {
                        case null: window.location.reload(); break;
                        default: break;
                    }
                });
            });
        };

        return BackofficeThread;

    })();



    /**
     * Представление раздела чтения сообщения
     * @type {SectionRead}
     */
    const SectionRead = (function() {

        /**
         * Конструктор
         * @param {ynoApplication} app Представление приложения
         * @param {HTMLElement} section Раздел
         * @constructor
         */
        function SectionRead(app, section) {
            this._app = app; this._section = section;
            this._forms = {
                token: this._section.find('form[data-for="token"]'),
                message: this._section.find('form[data-for="message"]'),
            };
            this._conversation = {
                title: this._section.find('[data-for="conversation-title"]'),
                container: this._section.find('[data-for="conversation-messages"]'),
                template: this._app.hbTemplate('message-template'),
            };
            this._thread = null;
            this._get_conversation();
            this._send_message();
        }

        /**
         * Загружает сообщения токена
         * @param {String} thread Идентификатор токена
         * @private
         */
        SectionRead.prototype._load_conversation = function(thread) {
            const self = this;
            const request = { thread: thread };
            const displayError = function(value) {
                const block = self._forms.token.find('[data-for="error"]');
                switch (value) {
                    case true: block.show(); self._forms.message.hide(); break;
                    case false: block.hide(); self._forms.message.show(); break;
                }
            };
            self._app.api('/conversation', request, function(result, error) {
                self._conversation.container.html('');
                switch (error) {
                    case null:
                        displayError(false);
                        self._thread = result['uuid'];
                        self._conversation.title.html(result['title']);
                        Object.keys(result['messages']).forEach(function(id) {
                            const element = $(self._conversation.template(result['messages'][id]));
                            self._conversation.container.append(element);
                        });
                        break;
                    default:
                        displayError(true);
                        self._thread = null;
                        self._conversation.title.html('');
                        break;
                }
            });
        };

        /**
         * Обрабатывает форму загрузки сообщений
         * @private
         */
        SectionRead.prototype._get_conversation = function() {
            const self = this;
            const input = this._forms.token.find('#read-message-token');
            this._forms.token.submit(function(event) {
                event.preventDefault();
                const token = input.val();
                self._load_conversation(token);
            });
        };

        /**
         * Обрабатывает форму отправки сообщения
         * @private
         */
        SectionRead.prototype._send_message = function() {
            const self = this;
            const form = this._forms.message;
            const input = form.find('#message');
            const button = form.find('button[type="submit"]');
            form.submit(function(event) {
                event.preventDefault(); button.attr('disabled', true);
                const request = { thread: self._thread, message: input.val() };
                self._app.api('/message/insert', request, function(result, error) {
                    button.removeAttr('disabled');
                    switch (error) {
                        case null:
                            input.val(''); // Сбросим значение
                            self._load_conversation(self._thread);
                            break;
                        default:
                            break;
                    }
                });
            });
        };

        /**
         * Готовит и переключает форму отправки
         * @param {Function} callback Обработчик обратного вызова
         */
        SectionRead.prototype.focus = function(callback) {
            this._section.show();
            // @todo На мобилке очень некрасиво выскакивает клавиатура
            // this._forms.token.find('#read-message-token').focus();
            callback();
        };

        return SectionRead;

    })();



    /**
     * Представление раздела отправки сообщения
     * @type {SectionWrite}
     */
    const SectionWrite = (function() {

        /**
         * Конструктор
         * @param {ynoApplication} app Представление приложения
         * @param {HTMLElement} section Раздел
         * @constructor
         */
        function SectionWrite(app, section) {
            this._app = app; this._section = section;
            this._form = this._section.find('form');
            this._elements = {
                token: this._form.find('#write-message-token'),
                refreshButton: this._form.find('#change-token'),
                messageTitle: this._form.find('#write-message-title'),
                messageText: this._form.find('#write-message-text'),
            };
            // Тут будем хранить описание токана для отправки сообщения
            this._token = { token: null, signature: null, expires: null };
            this._refresh_token(); // Подпишем кнопку обновления токена
            this._submit_form(); // Подпишем форму отправки сообщения
            this._change_message(); // Подпишем изменения поля сообщения
        }

        /**
         * Отобращает или скрывает ошибку
         * @param {String|null} message Сообщение
         * @private
         */
        SectionWrite.prototype._error = function(message) {
            const element = this._form.find('[data-for="error"]');
            switch (message) {
                case null: element.hide(); break;
                default: element.html(message).show(); break;
            }
        };

        /**
         * Обрабатывает клик на кнопку "обновить токен"
         * @private
         */
        SectionWrite.prototype._refresh_token = function() {
            const self = this;
            this._elements.refreshButton.bind('click', function() {
                self._elements.refreshButton.attr('disabled', true);
                self._get_token(function() { // Новое значение
                    self._elements.refreshButton.removeAttr('disabled');
                    self._error(null); // Скроем возможную ошибку
                });
            });
        };

        /**
         * Обрабатывает изменение поля ввода сообщения
         * @private
         */
        SectionWrite.prototype._change_message = function() {
            const self = this;
            this._elements.messageText.on('keyup', function() {
                self._error(null); // Сбросим, если меняется текст
            });
        };

        /**
         * Обрабатывает отправку формы
         * @private
         */
        SectionWrite.prototype._submit_form = function() {
            const self = this;
            const getRequest = function() {
                let result = {}; // Сюда накопим результат!
                const keys = ['token', 'expires', 'signature'];
                Object.keys(keys).forEach(function(id) {
                    result[keys[id]] = self._token[keys[id]];
                });
                // Ну и осталось добавить только заголовок и текст
                result['title'] = self._elements.messageTitle.val();
                result['message'] = self._elements.messageText.val();
                return result;
            };
            const successResult = function() {
                const parent = self._form.parent();
                const section = parent.find('[data-for="success"]');
                section.find('[data-for="token"]').html(self._token.token);
                self._form.hide(); section.show();
            };
            const button = this._form.find('button[type="submit"]');
            this._form.submit(function(event) {
                event.preventDefault();
                button.attr('disabled', true);
                self._app.api('/message/post', getRequest(), function(result, error) {
                    button.removeAttr('disabled');
                    switch (error) {
                        case null: successResult(); break;
                        default: self._error(error.message); break;
                    }
                });
            });
        };

        /**
         * Получает и устанавнивает новый токен сообщения
         * @param {Function} callback Функция обратного вызова
         * @private
         */
        SectionWrite.prototype._get_token = function(callback = null) {
            const self = this;
            this._app.api('/token', {}, function(result, error) {
                switch (error) {
                    case null:
                        self._token = result; // Зададим значение
                        self._elements.token.val(self._token.token);
                        if (typeof callback === 'function') { callback(); }
                        break;
                    default:
                        break;
                }
            });
        };

        /**
         * Готовит и переключает форму отправки
         * @param {Function} callback Обработчик обратного вызова
         */
        SectionWrite.prototype.focus = function(callback) {
            const self = this;
            this._get_token(function() {
                self._section.show();
                self._elements.messageTitle.focus();
                callback();
            });
        };

        return SectionWrite;

    })();



    exports.ynoApplication = ynoApplication;



}(typeof exports === 'object' && exports || this));
