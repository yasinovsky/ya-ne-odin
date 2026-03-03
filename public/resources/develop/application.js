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
        }

        /**
         * Обработчик главной страницы
         * @private
         */
        ynoApplication.prototype.main = function() {
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
                const view = $('[data-view="read"]');
                const controller = new SectionRead(view);
                controller.focus(function() {
                    leave();
                })
            });
            buttons.write.bind('click', function() {
                const view = $('[data-view="write"]');
                const controller = new SectionWrite(view);
                controller.focus(function() {
                    leave();
                });
            });
        };

        return ynoApplication;

    })();



    /**
     * Представление раздела чтения сообщения
     * @type {SectionRead}
     */
    const SectionRead = (function() {

        /**
         * Конструктор
         * @param {HTMLElement} section Раздел
         * @constructor
         */
        function SectionRead(section) {
            this._section = section;
        }

        /**
         * Готовит и переключает форму отправки
         * @param {Function} callback Обработчик обратного вызова
         */
        SectionRead.prototype.focus = function(callback) {
            this._section.show();
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
         * @param {HTMLElement} section Раздел
         * @constructor
         */
        function SectionWrite(section) {
            this._section = section;
        }

        /**
         * Готовит и переключает форму отправки
         * @param {Function} callback Обработчик обратного вызова
         */
        SectionWrite.prototype.focus = function(callback) {
            this._section.show();
            callback();
        };

        return SectionWrite;

    })();



    exports.ynoApplication = ynoApplication;



}(typeof exports === 'object' && exports || this));
