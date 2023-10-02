/**
 * Created by Сергей on 05.07.2018.
 */

/**
 * Общие переменные
 */
var live = 5000,
    // для функции number_format
    co = 2, fl = '.', th = '';
/**
 * Сумма, с которой начинается вычисляться скидка
 * Если при высчислениях сумма больше либо равно минимальному порогу скидки
 * то начинаем вычислять скидку
 * если нет, то скидки нет
 */
var minDiscountActive = 5000;

// Пороги скидок. Настройки порогов скидок в объекте
/**
 * Эти значения нужно будет задать в PHP, в настройках сайта
 * и передать в JS
 * ---------------------------------------------------------
 * dt - discount thresholds(дисконтные пороги)
 */
var dt = {
    "d5": [5, minDiscountActive],
    "d10": [10, 15000],
    "d15": [15, 30000],
    "d20": [20, 50000]
};

/**
 * FUNCTIONS
 *
 * Вспомогательные функции
 */

// Всплывающие Alert'ы
function LoadAlert(head_text, body_text, live, color) {
    if (color == 'error') {
        color_bg = 'al-danger';
    } else if (color == 'warning') {
        color_bg = 'al-warning';
    } else {
        color_bg = 'al-success';
    }
    $(function () {
        $.jGrowl(body_text, {
            header: head_text,
            theme: color_bg,
            position: 'bottom-right',
            life: live
        });
    });
}

// всплывающие bootstrap подсказки
function popUp(selector, text, type) {
    var sw = selector + ' .alert',
        stw = selector + ' .alert .text',
        w = $(sw),
        tw = $(stw);

    // по умолчанию всегда будет зеленое окно success
    switch (type) {
        case 'info':
            type = 'info';
            break;
        case 'warning':
            type = 'warning';
            break;
        case 'danger':
            type = 'danger';
            break;
        default: type = 'success';
    }
    w.addClass('alert-' + type);

    w.fadeIn(100);
    tw.html(text);
}

/**
 * Закрываем окно об обшибках
 *  cea - Clear Error Alert
 */
function cea(wrap) {
    if (typeof wrap === 'undefined') wrap = '';
    else wrap += ' ';
    $(wrap + '.alert .close').trigger('click');
}

/**
 * Вспомогательная функция
 * для функции сортировки массива sort
 */
function compareNumeric(a, b) {
    return a - b;
}

// проверка на цифры
function isNumeric(input, type) {
    switch (type) {
        case 'n.':
            // цифры, точки
            inputV = input.value.replace(/[^\d\.]/g, '');
            input.value = roundToTwo(inputV, '.');
            break;
        case 'n,':
            // цифры, запятые
            inputV = input.value.replace(/[^\d\,]/g, '');
            input.value = roundToTwo(inputV, ',');
            break;
        // цифры
        default: input.value = input.value.replace(/[^\d]/g, '');
    }
}

/**
 * После символа (запятая/точка)
 * оставляем только два знака
 * @param   {string}   str Обязательный аргумент
 * @param   {string} symbol Обязательный аргумент
 * @returns {string} 
 */
function roundToTwo(str, symbol) {
    // по шаблону ищем в строке искомый символ
    var tpl = new RegExp(symbol, 'g');
    var result = str.match(tpl);

    // считаем количество символов symbol
    var counter = [];
    for (i in result) {
        if (counter[result[i]] != undefined) (counter[result[i]]++)
        else (counter[result[i]] = 1)
    }

    /**
     * Дополнительная проверка
     * на всякий случай проверим на null
     */
    if (result !== null) {
        // поверяем, есть ли в массиве искомый символ
        if (result.indexOf(symbol) != -1) {
            // Если искомых символов всего один
            if (counter[symbol] == 1) {
                // получаем номер позиции первого искомого символа
                var pos = str.indexOf(symbol);
                /**
                 * Проверку переменной pos делать не нужно
                 * потому что если искомых символов не будет, то
                 * этот внешний блок if(result !== null) не запустится
                 */
                // берем символы после первого искомого символа
                var decimal = str.substr(pos + 1, 2);
                // берем символы до первого искомого символа
                var example = str.split(symbol);
                // составляем строку {string}+{symbol}+{string}
                str = example[0] + symbol + decimal;
            } else {
                /* 
                 * Если искомых символов больше одного
                 */
                // получаем номер позиции первого искомого символа
                var pos = str.indexOf(symbol);
                // берем символы до первого искомого символа
                var example = str.split(symbol);
                /**
                 * К символам взятым до первого искомого символа
                 * в конец подставляем искомый символ
                 * =============================================
                 * составляем строку {string}+{symbol}
                 */
                str = example[0] + symbol;
            }
        }
    }
    return str;
}

/**
 * Убираем все предупреждающие стили поля input
 */
function focusReturnStyle($$this) {
    $($$this).removeClass('warningInput');
}

/***
 number - исходное число
 decimals - количество знаков после разделителя
 dec_point - символ разделителя сотых
 thousands_sep - разделитель тысячных
 синтаксис - number_format(totalSumm, 2, ',', ' ')
 ***/
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + (Math.round(n * k) / k)
                .toFixed(prec);
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
        .split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '')
        .length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1)
            .join('0');
    }
    return s.join(dec);
}

function getNumber(str) {
    if (str === undefined) return str;
    return Number(number_format(str, 2, '.', ''));
}

/**
 * Проверка объекта на пустоту
 */
function isEmpty(object) {
    for (var key in object)
        if (object.hasOwnProperty(key)) return true;

    return false;
}

// Получение UNIX времени
function unixTime() {
    return parseInt(new Date().getTime() / 1000);
}

// надо разобраться в этой функции
//function roundNumber(num, scale) {
//	if(!("" + num).includes("e")) {
//		return +(Math.round(num + "e+" + scale)  + "e-" + scale);
//	} else {
//		var arr = ("" + num).split("e");
//		var sig = ""
//		if(+arr[1] + scale > 0) {
//			sig = "+";
//		}
//		return +(Math.round(+arr[0] + "e" + sig + (+arr[1] + scale)) + "e-" + scale);
//	}
//}

/**
 * Запуск модального окна
 * @param header - заголовок
 * @param body - тело окна
 */
function modalLarge(header, body) {
    // Вставляем текст в заголовок окна
    $('.modal-large .modal-title').html(header);
    // Вставляем текст в тело окна
    $('.modal-large .modal-body').html(body);
    /**
     * Вызываем клик на скрытой кнопке
     * показываем модальное окно
     */
    $('button[data-toggle=modal]').trigger('click');
}

// Вывод в консоль
function cl(arg1, arg2) {
    if (arg2 === undefined) console.log(arg1);
    else console.log(arg1, arg2);
}

// Проверка на строку
function isString(data) {
    if (typeof data === 'undefined') return false;
    if (typeof data === "string") return true;
    else return false;
}

// Проверка на массив
function isArray(data) {
    if (typeof data === 'undefined') return false;
    if (Array.isArray(data)) return true;
    else return false;
}

// Проверка на объект
function isObject(data) {
    if (typeof data === 'undefined') return false;
    if ((typeof data === "object" && !Array.isArray(data) && data !== null)) {
        return true;
    } else return false;
}

// Удаление \r\n, \r, \n из строки
function cleanRN(str) {
    var find = "\\r\\n";
    var regex = new RegExp(find, "g");
    str = str.replace(regex, '');

    var find2 = "\\r";
    var regex2 = new RegExp(find2, "g");
    str = str.replace(regex2, '');

    var find3 = "\\n";
    var regex3 = new RegExp(find3, "g");
    str = str.replace(regex3, '');
    return str;
}


/* ===========================================
               Document ready
============================================== */
$(function () {

    /**
     * Закрытие всех подсказок alert
     * по нажатию на крестик
     */
    $('.alert .close').on('click', function () {
        var classes = 'alert-success alert-info alert-warning alert-danger';
        $(this).parent().fadeOut(100).removeClass(classes);
    });
    /**
     * Закрытие всех подсказок alert
     * по нажатию на кнопку "Ок"
     */
    $('.alert button').on('click', function () {
        var classes = 'alert-success alert-info alert-warning alert-danger';
        $(this).parent().fadeOut(100).removeClass(classes);
    });

    // удаляем со страницы выделенные строки
    $('.sales-reciept .delete,.gr .delete').on('click', function () {
        // собираем все поля "input,textarea,select" - в объкект
        $('.sales-reciept,.gr').find('input:checkbox:checked').each(function () {
            $(this).parent().parent().fadeOut(100).remove();
        });
        if ($(this).attr('data-type') == 'goods-receipt') {
            // Пересчитываем количество штук товаров на странице
            var quantity = $('.gr .quantity'),
                l = $('.gr .table tbody tr').length;
            quantity.html(l);

            /**
             * Пересчитываем общие суммы на странице
             * себестоимость/розничная стоимость
             */
            var totalCostPrice = $('.gr .t-cost-price'),
                totalRetailPrice = $('.gr .t-retail-price'),
                costPriceItogSumm = 0,
                retailPriceItogSumm = 0,
                ii = 1;

            $('.gr .table tbody').find('tr').each(function () {
                var costPrice = $(this).find('.cost-price'),
                    retailPrice = $(this).find('.retail-price'),
                    amount = $(this).find('.amount');

                costPriceItogSumm += (Number(amount.val()) * Number(costPrice.val())),
                    retailPriceItogSumm += (Number(amount.val()) * Number(retailPrice.val()));

                $(this).find(':first span').html(ii);
                $(this).find(':first input').val(ii);
                ii++;

            });

            totalCostPrice.html(costPriceItogSumm);
            totalRetailPrice.html(retailPriceItogSumm);

        }
    });

    // Отключаем стандартное поведение ссылки
    $('.no-link').on('click', function (e) { e.preventDefault(); });

    // Всплывающие подксказки из вопросика
    $('[id=popover]').popover({
        container: 'body',
        placement: 'top',
        html: 'text'
    });
    $('[id=popover]').hover(function () {
        $(this).popover('show');
    }, function () {
        $(this).popover('hide');
    });


    /**
    * Замыкание (округдение чисел
    *    с возможнотью указания количества чисел после запятой)
    * Корректировка округления десятичных дробей.
    *
    * @param {String}  type  Тип корректировки.
    * @param {Number}  value Число.
    * @param {Integer} exp   Показатель степени (десятичный логарифм основания корректировки).
    * @returns {Number}  Скорректированное значение.
    */
    function decimalAdjust(type, value, exp) {
        // Если степень не определена, либо равна нулю...
        if (typeof exp === 'undefined' || +exp === 0) {
            return Math[type](value);
        }
        value = +value;
        exp = +exp;
        // Если значение не является числом, либо степень не является целым числом...
        if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
            return NaN;
        }
        // Сдвиг разрядов
        value = value.toString().split('e');
        value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
        // Обратный сдвиг
        value = value.toString().split('e');
        return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
    }

    // Десятичное округление к ближайшему
    if (!Math.round10) {
        Math.round10 = function (value, exp) {
            return decimalAdjust('round', value, exp);
        };
    }
    // Десятичное округление вниз
    if (!Math.floor10) {
        Math.floor10 = function (value, exp) {
            return decimalAdjust('floor', value, exp);
        };
    }
    // Десятичное округление вверх
    if (!Math.ceil10) {
        Math.ceil10 = function (value, exp) {
            return decimalAdjust('ceil', value, exp);
        };
    }


});//JQuery





























