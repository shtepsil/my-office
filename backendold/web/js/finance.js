$(function(){
    
    /**
     * ===================================================
     * Страница "Добавление отчета"
     * ===================================================
     */

    /**
     * Страница "Добавление отчета"
     * Страница "Истоия"
     * ============================
     * Поля "Введите дату" с календарем
     */
    // Дата прихода. Дата страницы "История"
    var dates = $(".fn #date-in,.history [name=date_history]").datepicker({
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрьr','Октябрь','Ноябрь','Декабрь'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        firstDay: 1,
        dateFormat: 'yy-mm-dd',
        maxDate: new Date(), // добавил вот эту строку
    });
    // Дата расхода
    var dates = $(".fn #date-out").datepicker({
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрьr','Октябрь','Ноябрь','Декабрь'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        firstDay: 1,
        dateFormat: 'yy-mm-dd',
        maxDate: new Date(), // добавил вот эту строку
    });

    /**
     * Страница "Добавление отчета"
     * Страница "Истоия"
     * Страница "Статистика WebMaster"
     * ============================
     * Поля "Введите дату" с календарем
     */
    // Дата прихода. Дата страницы "История"
    var dates = $(".fn [name=date_begin_month],.stcs [name=date_day]").datepicker({
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрьr','Октябрь','Ноябрь','Декабрь'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        firstDay: 1,
        dateFormat: 'yy-mm-dd',
        maxDate: new Date(), // добавил вот эту строку
    });

    /**
     * Страница "Добавление отчета"
     * ============================
     * Кнопка "Добавить"
     * Добавление строки в таблицу
     */
    $('.fn button[name=in],.fn button[name=out]').on('click',function(){
        var $this = $(this),
            res = $('.res'),
            form = $('.fn'),
            load = $this.find('img'),
            table1 = form.find('.table1 tbody'),
            table2 = form.find('.table2 tbody'),
            income = form.find('.w-income input[name=income]'),
            /**
             * Исключаем из выборки полей "input"ы,
             * имеющие атрибут "hidden" и у которых в "name" есть фраза date_
             * скрытые поля дата "приход/расход" строки
             * Чтобы при опустошении дата не исчезала
             * в добавленных строках отчета
             */
            fields = form
                .find('input,textarea,select')
                .not('.table tbody input,.w-income input[name=income],[type=checkbox]'),
            tags = form.find('.filter [name=tags]'),
            empty_field = false,
            wallets = form.find('[name=wallets]'),
            monthly = form.find('[name=monthly]'),
            invoice_clearing = form.find('[name=invoice_clearing]'),
            moving_accounts = form.find('[name=moving_accounts]'),
            wallets_from = form.find('[name=wallets_from]'),
            wallets_to = form.find('[name=wallets_to]'),
            itog_in = 0,
            itog_out = 0,
            Data = {};
        
        // Тип операции (приход/расход)
        Data['type'] = $this.attr('name');
        
        // Проверка, какая кнопка нажата
        // Если нажата кнопка "Добавить" приход
        if($this.attr('name') == 'in'){
            if(monthly.prop('checked')) Data['monthly_row'] = '1';
            else Data['monthly_row'] = '0';
            var type = 'in';
        }else{
            // Если нажата кнопка "Добавить" расход
            if(monthly.prop('checked')) Data['monthly_row'] = '2';
            else Data['monthly_row'] = '0';
            var type = 'out';}

        // Собираем данные полей (либо приход/доход либо расход)
        form.find('.wr-'+type).find('input,textarea,select').not('input[name=income]').each(function(){
            // Если одно из полей пусто
            if($(this).val() == ''){
//                cl($(this).attr('name')+' - '+$(this)[0].tagName+' - '+'.wr-'+type);
                empty_field = true;
                return false;
            }
            Data[$(this).attr('name')] = $(this).val();
        });
        
        // Проверка на пустоту
        if(empty_field){
            LoadAlert('Внимание','Заполнены не все поля',3000,'warning');
            return;
        }
        
        /**
         * Делаем проверку таблицы на присутствие строк перемещения.
         * Строки перемещения и доход/расход
         * одновременно в таблице находиться не могут
         */
        if(
            typeof table2.find('tr.info').html() !== 'undefined' &&
            !moving_accounts.prop('checked')
        ){
            LoadAlert('Внимание','Доход/расход<br>сейчас добавить нельзя<br>Сохраните документ и перезагрузите страницу!',3000,'warning');
            return;
        }
        
        /**
         * Если перемещение ВКЛЮЧЕНО, добавляем строку синего цвета
         * и добавляем сумму кошелька FROM
         * и сумму кошельа TO
         */
        if(moving_accounts.prop('checked')){
            
            // Проверяем FROM и TO на пустоту
            if(wallets_from.val() == '' || wallets_to.val() == ''){
               LoadAlert('Внимание','Выберите все кошельки<br>FROM и TO',3000,'warning');
                return;
            }
            
            /**
             * Поле "Комиссия за перевод"
             * Заменяем запятые на точки
             */
            var precentage = form.find('[name=percentage]').val().replace(',', '.');
            
            Data['moving_wallet_from'] = wallets_from.val();
            Data['moving_wallet_to'] = wallets_to.val();
            Data['moving_wallet_from_ammount'] = wallets_from.find('option:selected').attr('data-balance');
            Data['moving_wallet_to_ammount'] = wallets_to.find('option:selected').attr('data-balance');
            Data['percentage'] = (precentage != '')?precentage:'0';
            // Определение цвета стоки в таблице
            Data['moving_accounts'] = '1';
        }
        
        /**
         * Если выравнивание счета выключено
         * то делаем проверку - выбран ли кошелек
         */
        if(!invoice_clearing.prop('checked')){
            // Проверка списка "Кошельки" на пустоту
            if(wallets.val() == '' && !moving_accounts.prop('checked')){
                LoadAlert('Внимание','Выберите кошелек',3000,'warning');
                return;
            }
            
            // Добавляем кошелек
            Data['wallet_name'] = wallets.find('option:selected').text();
            Data['wallet'] = wallets.val();
            Data['wallet_balance'] = wallets.find('option:selected').attr('data-balance');
            
        }
        
        // Если выбрана метка
        if(tags.val() != ''){
            Data['tags'] = tags.val();
            Data['tags_name'] = tags.find('option:selected').text();
        }
        
        // Если отмечен чекбокс "Доход"
        if(income.prop('checked')) Data['income'] = income.val();
        
//        cl(Data);
//        return;
        
        $.ajax({
            url:$this.attr('action'),
            type:$this.attr('method'),
            cashe:'false',
            dataType:'json',
            data:Data,
            beforeSend:function(){
                load.fadeIn(100);
            }
        }).done(function(data){
//            res.html('Done<br>'+JSON.stringify(data));
//            LoadAlert(data.header,data.message,live,data.type_message);
            if(data.status == 200){
                
                // Если в table есть строка "пока пусто", то удаляем её
                if(typeof table2.find('.empty').attr('class') !== 'undefined'){
                    table2.find('.empty').hide().remove();
                }
                
                // Добавляем строку tr
                table2.prepend(data.row);
                /**
                 * Опустошаем все поля,
                 * кроме чекбоксов
                 */
                fields.val('');
                
                /**
                 * Выпадающие списки с кошельками
                 * Кошелек FROM кошелёк TO
                 * Делаем активными все options
                 * и сбрасываем цвет текста на черный
                 */
                wallets_from.find('option').prop('disabled',false).css({color:'inherit'});
                wallets_to.find('option').prop('disabled',false).css({color:'inherit'});
                
//                form.find('[type=checkbox]').prop('checked','');
                
                // Чекбокс "Доход" делаем пустым
                income.prop('checked','');
                // Чекбокс "Ежемесячный платеж" делаем пустым и неактивным
                monthly.prop('checked','').prop('disabled',true);
                // Чекбокс "Выравнивание" делаем пустым
                invoice_clearing.prop('checked','');
                
                // Если отмечено перемещение
                if(moving_accounts.prop('checked')){
                   // Выпадающее поле "Метки" ставим на "Перемещение"
                    tags.val('move');
                }else{
                    // Сбрасываем выпадающее поле "Метки"
                    tags.prop('disabled','').val('other');
                }
                
                // Перебираем все строки для счета общих сумм
                table2.find('tr').each(function(){
                    if($(this).attr('data-type') == 'in')
                        // Считаем приход
itog_in += Number($(this).find('.ammount').html().replace(',', '.'));
                    else
                        // Считаем расход
itog_out += Number($(this).find('.ammount').html().replace(',', '.'));
                });

                itog_in = number_format(itog_in, 2, ',', ' ');
                itog_out = number_format(itog_out, 2, ',', ' ');
                table1.find('.itog-in b').html(itog_in);
                table1.find('.itog-out b').html(itog_out);
                
            }
            load.fadeOut(100);
        }).fail(function(data){
//            res.html('Fail<br>'+JSON.stringify(data));
            LoadAlert('Error','Ошибка PHP',live,'error');
            load.fadeOut(100);
        });

    });

    /**
     * Страница "Добавление отчета"
     * ============================
     * Поле "Введите сумму"
     * --------------------
     * checkbox - "Ежемесячный платеж"
     * 1 - это приход
     * 2 - это расход
     * Чекбокс "Ежемесячный платеж" становится активным
     * если что то вводим в поле "Введите сумму" приход/расход
     * ---
     * Если поле "Приход" в режиме перемещения,
     * то чекбокс "Ежемесячный платеж" активным делать не будем.
     */
    
//    /** 
//     * ---- Это стало не нужно ----
//     * =======================================
//     * доступным может быть только для расхода
//     * Приход ежемесячным быть не может
//     * =======================================
//     * ---- Это стало не нужно ----
//     */
    
    $(`
        .fn .wr-in input[name=ammount],
        .fn .wr-out input[name=ammount]
    `)
        .on('input change',function(){
        var $this = $(this),
            monthly = $('[name=monthly]'),
            moving_accounts = $('[name=moving_accounts]');
        // Если текущее поле не пусто
        if($this.val() != ''){
            // Если чекбокс "Перемещение" не отмечен
            if(!moving_accounts.prop('checked')){
                // Чекбокс "Ежемесячный платеж" делаем активным
                monthly.prop('disabled','');
            }
        }else monthly.prop('checked','').prop('disabled',true);
    });

    /**
     * Страница "Добавление отчета"
     * ============================
     * Выпадающий список "Кошельки"
     * ----------------------------
     * Чекбокс "Выравнивание счета" делаем пустым
     */
    $('.fn [name=wallets]').on('change',function(){
        $('[name=invoice_clearing]').prop('checked','');
    });

    /**
     * Страница "Добавление отчета"
     * ============================
     * Чекбокс "Выравнивание счета"
     * ----------------------------
     */
    $('.fn input[name=invoice_clearing]').on('change',function(){
        var $this = $(this),
            wallets = $('[name=wallets]'),
            tags = $('[name=tags]');
        
        // Если отмечен
        if($this.prop('checked')){
            // Сбрасываем список кошельки
            wallets.val('');
            
            /**
             * Метки ставим в "Выравнивание баланса"
             * и делаем недоступным для изменения
             */
            tags.val('equation').prop('disabled',true);
        }else{
            // Метки делаем доступными
            tags.val('other').prop('disabled','');
        }
        
    });

    /**
     * Страница "Добавление отчета"
     * ============================
     * Чекбокс "Перемещение по счетам"
     * -------------------------------
     * Если отмечен - активируем выпадающие
     * списки для перемещения "Кошельки"
     */
    $('.fn .w-moving-accounts input[name=moving_accounts]').on('change',function(){
        var $this = $(this),
            form = $('.fn'),
            table2 = form.find('.table2 tbody'),
            income = form.find('[name=income]'),
            tags = form.find('[name=tags]'),
            wallets = form.find('[name=wallets]'),
            monthly = form.find('[name=monthly]'),
            w_out = form.find('.wr-out'),
            invoice_clearing = form.find('[name=invoice_clearing]'),
            percentage = form.find('[name=percentage]'),
            w = form.find('.w-moving-accounts');
        
        /**
         * Если в таблицу уже добавлены строки доход/расход
         * то выдаем сообщение об ошибке и оставляем текущий чекбок пустым
         */
        if(typeof table2.find('.success,.warning').html() !== 'undefined'){
            LoadAlert('Внимание','В таблице присутствую строки доход/расход<br>Сохраните документ и перезагрузите страницу!',5000,'warning');
            $this.prop('checked','');
            return;
        }
        
        /**
         * Если в таблицу уже добавлены строки перемещения
         * то выдаем сообщение об ошибке и оставляем текущий чекбок отмеченым
         */
        if(typeof table2.find('.info').html() !== 'undefined'){
            LoadAlert('Внимание','В таблице уже присутствуют<br>строки перемещения<br>Сохраните документ и перезагрузите страницу!',5000,'warning');
            $this.prop('checked',true);
            return;
        }
        
        // Если включаем перемещение
        if($this.prop('checked')){
//            cl('вкл');
            w.find('select').prop('disabled','');
            tags.val('move').prop('disabled',true);
            wallets.val('').prop('disabled',true);
            monthly.prop('checked','').prop('disabled',true);
            invoice_clearing.prop('checked','').prop('disabled',true);
            percentage.val('').prop('disabled','');
            
            w_out.find('input,textarea,select').val('').prop('disabled',true);
            w_out.find('button').prop('disabled',true);
            
            income.prop('checked','').prop('disabled',true);
            
        }else{
//            cl('выкл');
            w.find('select').val('').prop('disabled',true);
            tags.val('other').prop('disabled','');
            wallets.prop('disabled','');
//            monthly.prop('disabled','');
            invoice_clearing.prop('disabled','');
            percentage.val('').prop('disabled',true);
            
            income.prop('checked','').prop('disabled',false);
            
            w_out.find('input,textarea,select,button').prop('disabled','');
        }
    });
    
    /**
     * Страница "Добавление отчета"
     * ============================
     * Выпадающие списки "Кошельки" FROM и TO
     */
    $('.fn .w-moving-accounts select').on('change',function(){
        var $this = $(this),
            form = $('.fn');
        if($this.attr('name') == 'wallets_to'){
            var select_opposite = form.find('[name=wallets_from]');
        }else var select_opposite = form.find('[name=wallets_to]');
        
        select_opposite.find('option').each(function(){
            if($(this).val() == $this.val() && $(this).val() != ''){
                $(this).css({'color':'rgba(255,0,0,.4)'}).prop('disabled',true);}
            else $(this).css({'color':'inherit'}).prop('disabled','');
        });
        
//        s_opposite.
        
    });
    
    /**
     * Страница "Добавление отчета"
     * ============================
     * Выпадающий список "Кошельки" TO
     */
    $('.fn select[name=wallets_to]').on('change',function(){
        var $this = $(this),
            form = $('.fn'),
            w_from = form.find('[name=wallets_from]');
    });

    /**
     * Страница "Добавление отчета"
     * ============================
     * Кнопка "Сохранить отчет"
     */
    $('.fn button.save').on('click',function(){
        var $this = $(this),
            res = $('.res'),
            form = $('.fn'),
            load = $this.find('img'),
            table2 = form.find('.table2 tbody'),
            invoice_clearing = form.find('[name=invoice_clearing]'),
            income = form.find('[name=income]'),
            disabled_table = form.find('.disabled-table'),
            moving_accounts = form.find('[name=moving_accounts]'),
            development = form.find('[name=development]'),
//            table1 = form.find('.table1'),
//            fields = form.find('input,textarea'),
            empty_table = false,
            i = 0,
//            itog_out = 0,
            Data = {};
            Data['rows'] = {};
            Row = {};
        
        // Собираем данные строк таблицы
        table2.find('tr').each(function(){
            // Если таблица отчета пуста
            if($(this).attr('class') == 'empty'){
                empty_table = true;
                return false;
            }
            
            /**
             * Порядок добавления данных в итерационный объект Row
             * должен быть в таком же порядке, как и ячейки строки таблицы БД
             */
            Row['monthly'] = $(this).find('[name=monthly_row]').val();
            Row['ammount'] = $(this).find('.ammount').html().replace(',', '.');
            Row['comment'] = $(this).find('.comment').text();
            
            /**
             * Если выравнивание счета и перемещение выключены
             * то добавляем кошелек
             */
            if(!invoice_clearing.prop('checked')){
                Row['wallet_id'] = $(this).find('[name=wallet]').val();
                Row['wallet_balance'] = $(this).find('[name=wallet]').attr('data-balance');
            }
            
            Row['tags'] = $(this).find('.tags [name=tags]').val();
            Row['date_in'] = $(this).find('[name=date_in]').val();
            Row['date_out'] = $(this).find('[name=date_out]').val();
            Row['income'] = $(this).find('[name=income]').val();
            
            /**
             * Если включено перемещение
             * то добавляем кошелек FROM и кошелек TO
             */
            // Был ориентир по чекбоксу "перемещение"
//            if(moving_accounts.prop('checked')){
            // Стал ориентир по скрытому полю в row - name=moving_accounts
            if($(this).find('[name=moving_accounts]').val() != '0'){
                Row['moving_accounts'] = $(this).find('[name=moving_accounts]').val();
                Row['moving_wallet_from'] = 
                    $(this).find('[name=moving_accounts]').attr('data-wallet-from');
                Row['moving_wallet_from_ammount'] = 
                    $(this).find('[name=moving_accounts]').attr('data-wallet-from-ammount');
                Row['moving_wallet_to'] = 
                    $(this).find('[name=moving_accounts]').attr('data-wallet-to');
                Row['moving_wallet_to_ammount'] = 
                    $(this).find('[name=moving_accounts]').attr('data-wallet-to-ammount');
                Row['percentage'] = 
                    $(this).find('[name=moving_accounts]').attr('data-percentage');
            }
            
            Data['rows'][i] = Row;
            Row = {};
            i++;
        });
        
        // Проверка на пустоту
        if(empty_table){
            LoadAlert('Внимание','Таблица отчета пуста',3000,'warning');
            return;
        }
        
        if(development.prop('checked')){
           Data['development'] = 'development';
        }
        
//        cl(Data);
//        return;
        
        $.ajax({
            url:$this.attr('action'),
            type:$this.attr('method'),
            cashe:'false',
            dataType:'json',
            data:Data,
            beforeSend:function(){
                load.fadeIn(100);
            }
        }).done(function(data){
//            res.html('Done<br>'+JSON.stringify(data));
            LoadAlert(data.header,data.message,live,data.type_message);
            if(data.status == 200){
//                $this.prop('disabled',true);
//                disabled_table.fadeIn(200);
                // Сбрасываем, обнуляем всю страницу
                resetAddReport();
            }
            load.fadeOut(100);
        }).fail(function(data){
            res.html('Fail<br>'+JSON.stringify(data));
            LoadAlert('Error','Ошибка PHP',live,'error');
            load.fadeOut(100);
        });

    });
    
    /**
     * Страница "Добавление отчета"
     * ============================
     * При вводе суммы или даты или комментария,
     * если кнопка сохранения отчета отключена,
     * то опустошаем всю страницу
     */
    $('.fn input,.fn textarea').on('input',function(){
        var button_save = $('.fn button.save'),
            table1 = $('.table1 tbody'),
            table2 = $('.table2 tbody');
        
        if(button_save.prop('disabled')){
            button_save.prop('disabled','');
            table2.html(tr_empty);
            table1.find('.itog-in b').html(zeroz);
            table1.find('.itog-out b').html(zeroz);
        }
    });
    
    /**
     * Страница "Добавление отчета"
     * ============================
     * Кнопка "Открыть текущий месяц"
     */
    $('.fn [name=open_month]').on('click',function(){
        var $this = $(this),
            form = $('.fn'),
            load = $this.find('img'),
            res = form.find('.res'),
            date_begin_month = form.find('[name=date_begin_month]'),
            str_open_month = form.find('.open-month'),
            Data = {};
        
        if(date_begin_month.val() == ''){
            LoadAlert('Внимание','Не выбрана дата закрытия месяца',3000,'warning');
            return;
        }
        
        // Дата открытия месяца
        Data['date_open'] = date_begin_month.val();
        
//        console.log(JSON.stringify(Data));
//        return;
        
        $.ajax({
            url:$this.attr('data-url'),
            type:$this.attr('method'),
            cashe:'false',
            dataType:'json',
            data:Data,
            beforeSend:function(){ load.fadeIn(100); }
        }).done(function(data){
//            res.html('Done<br>'+JSON.stringify(data));
            LoadAlert(data.header,data.message,live,data.type_message);
            if(data.status == 200){
                str_open_month.find('.warning').fadeOut(100,function(){
                    str_open_month.find('.success span').html(data.total_amount);
                    str_open_month.find('.success').fadeIn(100);
                    date_begin_month.prop('disabled',true);
                    $this.prop('disabled',true);
                    form.find('button[name=in],button[name=out],button.save').prop('disabled','');
                });
            }
            load.fadeOut(100);
        }).fail(function(data){
//            res.html('Fail<br>'+JSON.stringify(data));
            LoadAlert('Error','Ошибка PHP',live,'error');
            load.fadeOut(100);
        });
    });
    
    /**
     * Страница "Добавление отчета"
     * ============================
     * Переключатель "Разработка"
     */
    $('[for=development]').on('click',function(){
        if(!$('[name=development]').prop('checked')){
            $('.fn .for-development').fadeIn(200);
        }else $('.fn .for-development').fadeOut(200);
    });

    /**
     * ===================================================
     * END Страница "Добавление отчета"
     * ===================================================
     */
    
    
    // ---------------------------------------------------
    // ---------------------------------------------------
    // ---------------------------------------------------
    
    
    /**
     * ===================================================
     * Страница "История"
     * ===================================================
     */
    
    /**
     * Страница "История"
     * ==================
     * Кнопочка "Открыть календари доходы/расходы"
     */
    $('.history .o-d-r').on('click',function(){
        $('.history .w-d-r').stop().animate({'height':'210px'},200).promise().done(function(){
            $('.history .o-d-r').fadeOut(100,function(){
                $('.history .c-d-r').fadeIn(100);
            });
        });
    });
    
    /**
     * Страница "История"
     * ==================
     * Кнопочка "Закрыть календари доходы/расходы"
     */
    $('.history .c-d-r').on('click',function(){
        $('.history .w-d-r').stop().animate({'height':'24px'},200).promise().done(function(){
            $('.history .c-d-r').fadeOut(100,function(){
                $('.history .o-d-r').fadeIn(100);
            });
        });
    });
    
    /**
     * Страница "История"
     * ==================
     * Поле "Введите дату"
     * -------------------
     * По изменению поля,
     * сбрасываем выпадающий список "Выберите период"
     * и поля "Отрезок времени"
     */
    $('[name=date_history]').on('change',function(){
        $('[name=period],[name=date_from],[name=date_to]').val('');
    });
    $('[name=date_history]').on('input',function(){
        $('[name=period],[name=date_from],[name=date_to]').val('');
    });
    
    /**
     * Страница "История"
     * ==================
     * Выпадающий список "Выберите период"
     * -----------------------------------
     * По изменению списка,
     * очищаем поле "Введите дату"
     * и поля "Отрезок времени"
     */
    $('[name=period]').on('change',function(){
        $('[name=date_history],[name=date_from],[name=date_to]').val('');
    });
    
    /**
     * Страница "История"
     * ==================
     * Выпадающий список "Выберите несколько меток"
     * --------------------------------------------
     * По изменению поля,
     * сбрасываем список "Метки"
     */
    $( ".history .multiple-tags" ).change(function () {
        $('.history .tags').val('');
        var selected_tags = "";
        $( ".multiple-tags option:selected" ).each(function() {
            selected_tags += '<span data-value="'+$(this).val()+'">'+$( this ).text() + "</span><br>";
        });
        $( ".selected-tags" ).html( selected_tags );
        $('.history .monthly').prop('checked',false);
    }).change();
    
    /**
     * Страница "История"
     * ==================
     * Выпадающий список "Тип операции доход/расход"
     * ---------------------------------------------
     * По изменению поля,
     * убираем галочку "Ежемесячная трата"
     */
    $('.history [name=type_operation]').change(function(){
        $('.history .monthly').prop('checked',false);
    });
    
    /**
     * Страница "История"
     * ==================
     * Чекбоксы Ежемесячный "Доход/Расход"
     * ---------------------------------------------
     * По изменению состояния чекбокса,
     * деактивируем поле "Показать всё приход/расход"
     */
    $('.history .mly-wrap #coming').change(function(){
        if($(this).prop('checked')){
            $('.history [name=type_operation]').val('').prop('disabled',true);
        }
        else{
            if(!$('.history .mly-wrap #costs').prop('checked'))
                $('.history [name=type_operation]').prop('disabled','');
        }
    });
    $('.history .mly-wrap #costs').change(function(){
        if($(this).prop('checked')){
            $('.history [name=type_operation]').val('').prop('disabled',true);
        }
        else{
            if(!$('.history .mly-wrap #coming').prop('checked'))
                $('.history [name=type_operation]').prop('disabled','');
        }
    });
    $('.history .income-wrap #income').change(function(){
        if($(this).prop('checked')){
            $('.history [name=type_operation]').val('').prop('disabled',true);
            $('.history .mly-wrap #costs').prop('checked','').prop('disabled',true);
            $('.history .mly-wrap #coming').prop('checked','').prop('disabled',true);
        }
        else{
            $('.history [name=type_operation]').prop('disabled','');
            $('.history .mly-wrap #costs').prop('checked','').prop('disabled','');
            $('.history .mly-wrap #coming').prop('checked','').prop('disabled','');
        }
    });
    
    /**
     * Страница "История"
     * ==================
     * Выпадающий список "Метки"
     * -------------------------
     * По изменению поля,
     * сбрасываем список "Выберите несколько меток"
     */
    $( ".history .tags" ).change(function () {
        $('.history .multiple-tags').val('');
        $('.history .selected-tags').html('');
        $('.history .monthly').prop('checked',false);
    });
    
    /**
     * Страница "История"
     * ==================
     * Кнопка "Сбросить фильтр"
     */
    $( ".history [name=reset_filter]" ).on('click',function () {
        var form = $('.history'),
            table1 = form.find('.table1');
        form.find('input,select').val('');
        form.find('.table2 tbody').html(tr_empty);
        table1.find('.itog-in b').html(zeroz);
        table1.find('.wallet-balance b').html(zeroz);
        table1.find('.itog-out b').html(zeroz);
        form.find('.monthly').prop('checked',false);
        
        // Сбрасываем недочеты
        form.find('.w-shortcoming .real-wallets span').each(function(){
            // Сбрасываем поля и span результатов
            $(this).prev().val('');
            $(this).html(zeroz);
        });
        // Итоговая сумма
        form.find('.w-shortcoming .itog-shortcoming').text(zeroz);
    });
    
    /**
     * Страница "История"
     * ==================
     * Кнопка "Сбросить выбранные метки"
     */
    $( ".history [name=reset_multiple_tags]" ).on('click',function () {
        $('.history .multiple-tags').val('');
        $('.history .selected-tags').html('');
    });
    
    /**
     * Страница "История"
     * ==================
     * Чекбокс "Ежемесячная трата"
     * ---------------------------
     * Если отмечен, то сбрасываем всё лишнее
     */
    $('.history .monthly').on('change',function() {
        if ($(this).is(':checked')) {
            $('.history [name=tags]').val('');
            $('.history .multiple-tags').val('');
            $('.history .selected-tags').html('');
            $('.history [name=type_operation]').val('');
        }
    });
    
    /**
     * Страница "История"
     * ==================
     * Поля "Диапозон дат"
     */
    var dates = $( ".history #date-from,.history #date-to" ).datepicker({
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрьr','Октябрь','Ноябрь','Декабрь'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        firstDay: 1,
        dateFormat: 'yy-mm-dd',
        maxDate: new Date(), // добавил вот эту строку
        onSelect: function( selectedDate ) {
            var option = this.id == "date-from" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date = $.datepicker.parseDate(
                instance.settings.dateFormat ||
                $.datepicker._defaults.dateFormat,
            selectedDate, instance.settings );
            dates.not( this ).datepicker( "option", option, date );
            // Сбрасываем поля "Выберите дату" и "Выберите период"
            $('.history [name=date_history],.history [name=period]').val('');
        }
    });
    
    /**
     * Страница "История"
     * ==================
     * Кнопка "Проверить недочет"
     */
    $('.history [name=check_shortcoming]').on('click',function(){
        var $this = $(this),
            form = $('.history'),
            load = $this.find('img'),
            res = form.find('.res'),
            empty_input = false,
            itog_shortcoming = 0,
            Data = {};
        
        // Расставляем недочеты
        form.find('.w-shortcoming .real-wallets span').each(function(){
            // Сбрасываем результаты
            form.find('.real-wallets .'+$(this).attr('class')).text(zeroz);
        });
        // Сбрасываем итог
        form.find('.w-shortcoming .itog-shortcoming').text(zeroz);
        
        // Собираем суммы реальных кошельков
        form.find('.w-shortcoming .real-wallets input').each(function(){
            // Пустые поля просто пропускаем
            if($(this).val() == '') return;
            Data[$(this).attr('name')] = $(this).val().replace(',','.');
        });
        
        if(JSON.stringify(Data) == '{}'){
            LoadAlert('Внимание','Должно быть заполнено хотябы одно поле',3000,'warning');
            return;
        }
        
//        console.log(JSON.stringify(Data));
//        return;
        
        $.ajax({
            url:$this.attr('data-url'),
            type:$this.attr('method'),
            cashe:'false',
            dataType:'json',
            data:Data,
            beforeSend:function(){ load.fadeIn(100); }
        }).done(function(data){
//            res.html('Done<br>'+JSON.stringify(data));
            
            /**
             * Преобразуем значение объекта data.json_calculations в строку JSON
             * и потом данные без лишних значений делаем назад в объект wallets
             */
            var wallets = JSON.parse(JSON.stringify(data.json_calculations));
            
            // Расставляем недочеты
            form.find('.w-shortcoming .real-wallets span').each(function(){
                // Незаполненные поля пропускаем
                if($(this).prev().val() == '') return;
                form.find(
                    '.real-wallets .'+$(this).attr('class'))
                    .text(number_format(wallets[$(this).attr('class')],2,',',''));
                itog_shortcoming += wallets[$(this).attr('class')];
            });
            
            // Вставляем сумму всех недочетов
            form.find(
                '.w-shortcoming .itog-shortcoming')
                .text(number_format(itog_shortcoming,2,',',''));
            
            LoadAlert(data.header,data.message,live,data.type_message);
            if(data.status == 200){
                
            }
            load.fadeOut(100);
        }).fail(function(data){
//            res.html('Fail<br>'+JSON.stringify(data));
            LoadAlert('Error','Ошибка PHP',live,'error');
            load.fadeOut(100);
        });
    });

    
    /**
     * ===================================================
     * END Страница "История"
     * ===================================================
     */
    
});//JQuery


// FUNCTIONS =============================================

/**
 * Страница "История"
 * ===================================================
 */

/**
 * Страница "История"
 * ==================
 * Кнопка "Найти"
 */
function getHistory(date_history,type_operation){
    var button_search = $('.history .get-history'),
        res = $('.res'),
        form = $('.history'),
        load = button_search.find('img'),
        table = form.find('.table2 tbody'),
        itog_in = form.find('.itog-in').find('b'),
        itog_out = form.find('.itog-out').find('b'),
        wallet_balance = form.find('.wallet-balance').find('b'),
        amount_of_income = form.find('.amount-of-income').find('b'),
        tags = form.find('[name=tags]'),
        wallets = form.find('[name=wallets]'),
        multiple_tags = form.find('.selected-tags'),
        monthly_in = form.find('.w-monthly #coming'),
        monthly_out = form.find('.w-monthly #costs'),
        income = form.find('.w-monthly #income'),
        itog_in_i = 0,
        itog_out_i = 0,
        Data = {};
    
    /**
     * Если аргументы функции пусты (дата и тип операций)
     * то берем их из элементов формы
     * ==================================================
     * Если поиск производится по кнопке "Найти"
     */
    if(typeof date_history === 'undefined' && typeof type_operation === 'undefined'){
        // Дата операций
        if(form.find('[name=date_history]').val() != ''){
            Data['date_history'] = form.find('[name=date_history]').val();
        }else Data['date_history'] = form.find('[name=period]').val();
        
        // Тип операций доход/расход
        type_operation = form.find('[name=type_operation]').val();
    }else Data['date_history'] = date_history;
    
    // Если выбран диапозон дат
    if(Data['date_history'] == ''){
        Data['date_range'] = {};
        Data['date_range']['date_from'] = form.find('#date-from').val();
        Data['date_range']['date_to'] = form.find('#date-to').val();
    }
    
    /**
     * Проверка на пустоту поля "Введите дату"
     * Выпадающего списка "Выберите период"
     * Полей "Отрезок времени" - FROM и TO
     * ====================================
     * Если одиночная дата или период не указаны
     * то проверяем поля "Отрезок времени"
     */
    if(Data['date_history'] == ''){
        if(Data['date_range']['date_from'] == '' || Data['date_range']['date_to'] == ''){
            LoadAlert('Внимание','Не указано время поиска',3000,'warning');
            return;
        }
    }
    
    // Сбрасываем данные шапки
    itog_in.html(zeroz);
    itog_out.html(zeroz);

    // Сбрасываем таблицу
    table.html(tr_empty);
    
    // Тип операции
    Data['type_operation'] = type_operation;
    
    // Если выбрана какая то метка
    if(tags.val() != ''){
        // Метка
        Data['tags'] = tags.val();
    }
    
    // Если выбран кошелек
    if(wallets.val() != ''){
        // Метка
        Data['wallet'] = wallets.val();
    }
    
    // Если есть список выбранных меток
    if(multiple_tags.html() != ''){
        Data['multiple_tags'] = {};
        var mti = 0;
        multiple_tags.find('span').each(function(){
            Data['multiple_tags'][mti] = $(this).attr('data-value');
            mti++;
        });
    }
    
    // Если отмечены "Ежемесячные движения"
    // Если приход
    if(monthly_in.prop('checked')) Data['monthly_in'] = true;
    // Если расход
    if(monthly_out.prop('checked')) Data['monthly_out'] = true;
    
    // Если отмечен чекбокс Доход(чисто заработанные)
    if(income.prop('checked')) Data['income'] = true;

//    cl(Data);
//    return;

    $.ajax({
        url:button_search.attr('action'),
        type:button_search.attr('method'),
        cashe:'false',
        dataType:'json',
        data:Data,
        beforeSend:function(){
            load.fadeIn(100);
        }
    }).done(function(data){
//        res.html('Done<br>'+JSON.stringify(data));
        LoadAlert(data.header,data.message,live,data.type_message);
        if(data.status == 200){
            // Если в table есть строка "пока пусто", то удаляем её
            if(typeof table.find('.empty').attr('class') !== 'undefined'){
                table.find('.empty').hide().remove();
            }

            // Добавляем строки tr
            table.append(data.tr_list);

            table.find('tr').each(function(){
                // Если это приход
                if($(this).attr('data-type') == '1'){
                    itog_in_i += Number($(this).find('.ammount').html());
                }
                if($(this).attr('data-type') == '2'){
                    itog_out_i += Number($(this).find('.ammount').html());
                }
            });

            // Вставляем данные в итоговые строки
            itog_in.html(number_format(itog_in_i, 2, ',', ' '));
            itog_out.html(number_format(itog_out_i, 2, ',', ' '));
            wallet_balance.html(number_format((itog_in_i-itog_out_i), 2, ',', ' '));
            if(data.opening_month_ammount != '0'){
                amount_of_income.html(number_format(
                    // Все доходы МИНУС Общая сумма открытия месяца
                    (itog_in_i - Number(data.opening_month_ammount)), 2, ',', ' '
                ));
            }

        }

        load.fadeOut(100);
    }).fail(function(data){
        res.html('Fail<br>'+JSON.stringify(data));
        LoadAlert('Error','Ошибка PHP',live,'error');
        load.fadeOut(100);
    });
}

/**
 * ===================================================
 * END Страница "История"
 */

/**
 * Страница "Добавление отчета"
 * ===================================================
 */

/**
 * Страница "Добавление отчета"
 * ============================
 * Редактирование строки таблицы
 * -----------------------------
 * Все данные возвращаем в поля редактирования
 * и строку удаляем из таблицы
 */
function editRow(obj){
    var $this = $(obj),
        form = $('.fn'),
        row = $this.parent().parent().parent(),
        table2 = $('.table2 tbody'),
        table1 = $('.table1 tbody'),
        itog_in = 0,
        itog_out = 0,
        type_operation = row.find('[name=type_operation]').val(),
        monthly = form.find('[name=monthly]'),
        wallets = form.find('[name=wallets]'),
        moving_accounts = form.find('[name=moving_accounts]'),
        wallet_from = form.find('[name=wallets_from]'),
        wallet_to = form.find('[name=wallets_to]'),
        percentage = form.find('[name=percentage]'),
        tag = form.find('.filter [name=tags]'),
        income = false;
    
//    cl(row.html());return;
    
    /**
     * Если тип операции "Доход"
     * то делаем выборку полей дохода
     */
    if(type_operation == '1'){
        var date_action = form.find('.wr-in [name=date_in]'),
            date_row = row.find('[name=date_in]'),
            comment = form.find('.wr-in [name=comment]'),
            amount = form.find('.wr-in [name=ammount]');
        
        income = form.find('.w-income [name=income]');
    }
    else{
    /**
     * Если тип операции "Расход"
     * то делаем выборку полей расхода
     */
        var date_action = form.find('.wr-out [name=date_out]'),
            date_row = row.find('[name=date_out]'),
            comment = form.find('.wr-out [name=comment]'),
            amount = form.find('.wr-out [name=ammount]');            
    }
    
    // Заполняем сумму
    amount.val(row.find('.ammount').html());
    if(income){
//        cl('-)'+row.find('[name=income]').val()+'(-');
        if(row.find('[name=income]').val() != ''){
            income.prop('checked',true);
        }
    }
    // Заполняем дату
    date_action.val(date_row.val());
    // Заполняем textarea комментария
    comment.val(row.find('.comment').html());
    // Вставляем в select метку
//    cl('-)'+row.find('.tags [name=tags]').val()+'(-');
    tag.val(row.find('.tags [name=tags]').val());
    
    // Если редактируемая строка - выравнивание счета
    if(row.find('.tags span').html() == 'equation'){
        // Чекбокс "Выравнивание счета" делаем отмеченным
        form.find('[name=invoice_clearing]').prop('checked',true);
        tag.prop('disabled',true);
    }else{
        // Выключаем выравнивание счета
        form.find('[name=invoice_clearing]').prop('checked','');
    }
    
    // Вставляем в select нужный кошелек
    wallets.val(row.find('[name=wallet]').val());
    
    /**
     * Если чекбокс "Перемещение" отмечен
     * то заполняем все элементы опции перемещения
     */
    if(moving_accounts.prop('checked')){
        
        // Заполняем кошелек FROM
        wallet_from.val(row.find('[name=moving_accounts]')
            .attr('data-wallet-from'));
        /**
         * В выпадающем списке FROM
         * блокируем кошелек(option), НА КОТОРЫЙ делается перемещение
         */
        wallet_from.find(
            'option[value='+
            row.find('[name=moving_accounts]').attr('data-wallet-to')+']'
        ).prop('disabled',true).css({color:'rgba(255, 0, 0, 0.4)'});

        // Заполняем кошелек TO
        wallet_to.val(row.find('[name=moving_accounts]')
            .attr('data-wallet-to'));
        /**
         * В выпадающем списке TO
         * блокируем кошелек(option), С КОТОРОГО делается перемещение
         */
        wallet_to.find(
            'option[value='+
            row.find('[name=moving_accounts]').attr('data-wallet-from')+']'
        ).prop('disabled',true).css({color:'rgba(255, 0, 0, 0.4)'});

        // Заполняем поле "Комиссия за перевод"
        percentage.val(number_format(row.find('[name=moving_accounts]').attr('data-percentage'),2,',',' '));
    }
    
    /**
     * Если редактируем "Приход", то проверяем
     * если это ежемесячный приход
     * то активируем checkbox "Ежемесячная плата" и отмечаем его
     */
    if(type_operation == '1'){
        monthly.prop('disabled','');
        if(row.find('[name=monthly_row]').val() == '1'){
            monthly.prop('checked',true);
        }
    }
    /**
     * Если редактируем "Расход", то проверяем
     * если это ежемесячный расход
     * то активируем checkbox "Ежемесячная плата" и отмечаем его
     */
    if(type_operation == '2'){
        monthly.prop('disabled','');
        if(row.find('[name=monthly_row]').val() == '2'){
            monthly.prop('checked',true);
        }
    }
    
    // Удаляем строку из таблицы с пересчетом общих сумм
    row.fadeOut(100,function(){
        row.remove().promise().done(function(){
            table2.find('tr').each(function(){
                if($(this).attr('data-type') == 'in')
itog_in += Number($(this).find('.ammount').html().replace(',', '.'));
                else
itog_out += Number($(this).find('.ammount').html().replace(',', '.'));
            });

            itog_in = number_format(itog_in, 2, ',', ' ');
            itog_out = number_format(itog_out, 2, ',', ' ');
            table1.find('.itog-in b').html(itog_in);
            table1.find('.itog-out b').html(itog_out);
        });
        if(table2.html() == '') table2.html(tr_empty);
    });
}

/**
 * Страница "Добавление отчета"
 * ============================
 * Удаление строки из таблицы
 */
function deleteRow(obj){
    var $this = $(obj),
        row = $this.parent().parent().parent(),
        table2 = $('.table2 tbody'),
        table1 = $('.table1 tbody'),
        itog_in = 0,
        itog_out = 0;
        
    row.fadeOut(100,function(){
        row.remove().promise().done(function(){
            table2.find('tr').each(function(){
                if($(this).attr('data-type') == 'in')
itog_in += Number($(this).find('.ammount').html().replace(',', '.'));
                else
itog_out += Number($(this).find('.ammount').html().replace(',', '.'));
            });

            itog_in = number_format(itog_in, 2, ',', ' ');
            itog_out = number_format(itog_out, 2, ',', ' ');
            table1.find('.itog-in b').html(itog_in);
            table1.find('.itog-out b').html(itog_out);
        });
        if(table2.html() == '') table2.html(tr_empty);
    });
}

/**
 * Страница "Добавление отчета"
 * ============================
 * Сброс всей страницы
 */
function resetAddReport(){
    var form = $('.fn'),
        table1 = $('.table1 tbody'),
        table2 = $('.table2 tbody'),
        monthly = form.find('[name=monthly]'),
        invoice_clearing = form.find('[name=invoice_clearing]'),
        wallets = form.find('[name=wallets]'),
        wallet_from = form.find('[name=wallets_from]'),
        wallet_to = form.find('[name=wallets_to]'),
        percentage = form.find('[name=percentage]'),
        tags = form.find('[name=tags]'),
        income = form.find('[name=income]');
    
    // Сбрасывем все input,select,textarea
    form.find('input,select,textarea')
        .not('[name=date_begin_month],[type=checkbox]')
        .val('').prop('disabled','');
    
    // Делаем кнопки "Добавить" активными
    form.find('[class*=wr-] button').prop('disabled',false);
    
    // Убираем галочки всех чекбоксов на странице
    form.find('[type=checkbox]').prop('checked','');
    
    // Чекбокса "Доход" делаем активным
    income.prop('disabled',false);
    
    // Отключаем поля "Перемещение по счетам"
    form.find('.w-moving-accounts').find('input,select').val('').prop('disabled',true);
    form.find('.w-moving-accounts input').prop('disabled','');
    
    // Все checkbox'ы делаем пустыми
    form.find('[type=checkbox]').prop('checked','');
    // Ежемесячный платеж делаем недоступным
    form.find('[name=monthly]').prop('disabled',true);
    // Выпадающий список "Метки" ставим по умолчанию
    tags.val('other');
    
    // Обнуляем общие суммы
    table1.find('.itog-in b').html(zeroz);
    table1.find('.itog-out b').html(zeroz);
    
    // Скрываем строку "Режим разработки"
    $('.fn .for-development').fadeOut(200);
    
    // Опустошаем таблицу
    table2.html(tr_empty);
}

/**
 * ===================================================
 * END Страница "Добавление отчета"
 */
