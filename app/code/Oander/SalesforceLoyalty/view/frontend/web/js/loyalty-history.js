/**
* Copyright 2019 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/
define([
    'jquery',
    'mage/translate',
], function($, $t) {
    "use strict";

    $.widget('mage.salesforceLoyaltyHistory', {
        options: {
            history: [],
        },
        size: 10,
        current: 0,

        /**
         * Initialize widget
         */
        _create: function () {
            this._loadDataToTable(0);
            this._createPagination();
            this._loadDataByPaginationIndex();
        },

        /**
         * Reduce history
         * @returns {Array}
         */
        _reduceHistory: function () {
            var self = this;
            var optionsByPage = [];

            if (this.options.history.AffiliatedTransactions.length) {
                optionsByPage = this.options.history.AffiliatedTransactions.reduce(function (prev, curr, i) {
                    var pageSize = Math.floor(i / self.size);
                    var page = prev[pageSize] || (prev[pageSize] = []);
                    page.push(curr);

                    return prev;
                }, []);
            }

            return optionsByPage;
        },

        /**
         * Create table
         * @param {Number} index
         * @returns {Void}
         */
        _loadDataToTable: function (index) {
            var self = this;
            var history = this._reduceHistory();
            var load = $('.block--loyaltyhistory > .block__load');
            var historyTable = $('.table--loyalty > tbody');

            if (history.length > 0) {
                $('.block--loyaltyhistory').removeClass('d-none');
            } else {
                $('.block--loyalty-account .profile-no-item').removeClass('d-none');
            }

            load.hide();
            historyTable.html('');

            for (var values in history) {
                if (values == index) {
                    for (var value in history[values]) {
                        var elem = history[values][value];
                        historyTable.append(self._createRow(elem.TransactionDate, elem.MagentoOrderNumber, elem.OrderId, elem.MMYOrderNumber, elem.TransactionType, elem.NoOfPoints));
                    }
                }
            }
        },

        /**
         * Create row
         * @param {String} date
         * @param {String} mOrderNumber
         * @param {String} mOrderId
         * @param {String} mmyOrderNumber
         * @param {String} type
         * @param {Number} points
         * @returns {HTMLElement}
        */
        _createRow: function (date, mOrderNumber, mOrderId, mmyOrderNumber, type, points) {
            var row = $('<tr></tr>'),
                newDate = new Date(date),
                year = newDate.getFullYear(),
                month = newDate.getMonth() + 1,
                day= newDate.getDate(),
                hour = newDate.getHours(),
                minutes = newDate.getMinutes();

            if (month < 10) month = '0' + month;
            if (day < 10) day = '0' + day;
            if (hour < 10) hour = '0' + hour;
            if (minutes < 10) minutes = '0' + minutes;

            row.append($('<td>'+year+'-'+month+'-'+day+' | '+hour+':'+minutes+'</td>'));

            if (mOrderNumber) {
                if (mOrderId) {
                    row.append($('<td class="morder"><a href="/sales/order/view/order_id/' + mOrderId + '/" target="_self">' + mOrderNumber +'</a></td>'));
                } else {
                    row.append($('<td class="morder">' + mOrderNumber +'</td>'));
                }
                row.append($('<td><span class="tooltip globe"><span class="tooltip__content">' + $t('Online purchase') + '</span></span></td>'));
            } else {
                row.append($('<td class="mmyorder">' + mmyOrderNumber + '</td>'));
                row.append($('<td><span class="tooltip store"><span class="tooltip__content">' + $t('In-store purchase') + '</span></span></td>'));
            }

            row.append($('<td>' + type + '</td>'));
            row.append($('<td>' + points + '</td>'));

            return row;
        },

        /**
         * Create pagination
         * @returns {Void}
         */
        _createPagination: function () {
            var self = this;
            var history = this._reduceHistory();
            var historyPagination = $('.block--loyaltyhistory > .block__pagination .items');

            historyPagination.append(self._createPaginationItem('prev', '<i class="icon icon-chevron-left"></i>'));

            for (var value in history) {
                historyPagination.append(self._createPaginationItem(Number(value) + 1));
            }
            
            historyPagination.append(self._createPaginationItem('next', '<i class="icon icon-chevron-right"></i>'));
        },

        /**
         * Create pagination item
         * @param {Number} index
         * @returns {HTMLElement}
         */
        _createPaginationItem: function (index, elem) {
            var item = $('<li class="item"></item>');

            if ((index - 1) == 0) {
                item = $('<li class="item current"></item>');
            }

            if (elem) {
                item.append('<a href="#" class="page" data-index="' + index + '">' + elem + '</a>');
            }
            else {
                item.append('<a href="#" class="page" data-index="' + (index - 1) + '">' + index + '</a>');
            }

            return item;
        },

        /**
         * Load data by pagination index
         * @returns {Void}
         */
        _loadDataByPaginationIndex: function () {
            var self = this;
            var paginationItem = $('.block--loyaltyhistory > .block__pagination .item > .page');

            paginationItem.on('click', function () {
                var index = $(this).attr('data-index');

                if (index === 'prev') {
                    if (self.current > 0) self.current = Number(self.current) - 1;
                }
                else if (index === 'next') {
                    if (self.current < (self._reduceHistory().length - 1)) self.current = Number(self.current) + 1;
                }
                else {
                    self.current = index;
                }

                paginationItem.closest('.item').removeClass('current');
                $('.page[data-index="' + self.current + '"]').closest('.item').addClass('current');

                self._loadDataToTable(self.current);
            });
        }
    });

    return $.mage.salesforceLoyaltyHistory;
});
