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
        modifiedTransactionsArray: [],

        /**
         * Initialize widget
         */
        _create: function () {
            var self = this;

            this._calculatePoints();
            this._loadDataToTable(0);

            if (self.options.history.AffiliatedTransactions.length > self.size) {
                self._createPagination();
                self._loadDataByPaginationIndex();
            }
        },

        /**
         * Calculate earned points
         * @returns {Void}
         */
        _calculatePoints: function() {
            var self = this,
                transatcionsArray = self.options.history.AffiliatedTransactions;

            transatcionsArray.reduce(function(previousItem, currentItem) {
                if (!previousItem[currentItem.MagentoOrderNumber] && currentItem.TransactionType === 'Points Earned') {
                    previousItem[currentItem.MagentoOrderNumber] = {
                        'TransactionType': currentItem.TransactionType,
                        'TransactionId': currentItem.TransactionId,
                        'TransactionDate': currentItem.TransactionDate,
                        'NoOfPoints': 0,
                        'MMYOrderNumber': currentItem.MMYOrderNumber,
                        'MagentoOrderNumber': currentItem.MagentoOrderNumber,
                        'InvoiceNumber': currentItem.InvoiceNumber,
                        'OrderId': currentItem.OrderId,
                    };

                    self.modifiedTransactionsArray.push(previousItem[currentItem.MagentoOrderNumber]);
                }

                if (currentItem.TransactionType !== 'Points Earned') {
                    self.modifiedTransactionsArray.push(currentItem);
                }

                if (typeof previousItem[currentItem.MagentoOrderNumber] !== 'undefined') {
                    if (previousItem[currentItem.MagentoOrderNumber].TransactionType === 'Points Earned' && currentItem.TransactionType === 'Points Earned') {
                        previousItem[currentItem.MagentoOrderNumber].NoOfPoints += currentItem.NoOfPoints;
                    }
                }

                return previousItem;
            }, {});

            // if (transatcionsArray.length) {
            //     var magentoOrderNumbers = [],
            //         multipleMagentoOrderNumbers = [],
            //         counter = {},
            //         checked = [],
            //         itemsOriginalIndexes = {};

            //     for (var i = 0; i < transatcionsArray.length; i++) {
            //         magentoOrderNumbers.push(transatcionsArray[i].MagentoOrderNumber)
            //     }

            //     for (var i = 0; i < magentoOrderNumbers.length; i++) {
            //         if (counter[magentoOrderNumbers[i]]) {
            //             counter[magentoOrderNumbers[i]] += 1;
            //         } else {
            //             counter[magentoOrderNumbers[i]] = 1;
            //         }

            //         if (counter[magentoOrderNumbers[i]] > 1 && multipleMagentoOrderNumbers.indexOf(magentoOrderNumbers[i]) < 0 && magentoOrderNumbers[i] !== null) {
            //             multipleMagentoOrderNumbers.push(magentoOrderNumbers[i]);
            //         }
            //     }

            //     for (var i = 0; i < transatcionsArray.length; i++) {
            //         if (multipleMagentoOrderNumbers.indexOf(transatcionsArray[i].MagentoOrderNumber) > -1) {
            //             if (checked.indexOf(transatcionsArray[i].MagentoOrderNumber) > -1) {
            //                 if (transatcionsArray[i].TransactionType === 'Points Earned') {
            //                     transatcionsArray[itemsOriginalIndexes[transatcionsArray[i].MagentoOrderNumber]].NoOfPoints += transatcionsArray[i].NoOfPoints;
            //                 } else {
            //                     self.modifiedTransactionsArray.push(transatcionsArray[i]);
            //                 }
            //             }
                        
            //             if (checked.indexOf(transatcionsArray[i].MagentoOrderNumber) < 0) {
            //                 if (transatcionsArray[i].TransactionType === 'Points Earned') {
            //                     itemsOriginalIndexes[magentoOrderNumbers[i]] = i;
            //                     checked.push(transatcionsArray[i].MagentoOrderNumber);
            //                 }

            //                 self.modifiedTransactionsArray.push(transatcionsArray[i]);
            //             }
            //         } else {
            //             self.modifiedTransactionsArray.push(transatcionsArray[i]);
            //         }
            //     }
            // }
        },

        /**
         * Reduce history
         * @returns {Array}
         */
        _reduceHistory: function () {
            var self = this,
                optionsByPage = [];

            if (self.modifiedTransactionsArray.length) {
                optionsByPage = self.modifiedTransactionsArray.reduce(function (prev, curr, i) {
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
