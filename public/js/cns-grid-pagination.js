/**
 * CNS DHTMLX Grid Server-Side Pagination & Controls
 * GEU ERP - CNS Module
 */
(function (window, $) {
    'use strict';

    function CnsGridPagination(grid, options) {
        this.grid = grid;
        this.options = $.extend({
            pageSize: 25,
            pageSizeOptions: [10, 25, 50, 100],
            containerId: null,
            gridContainerId: null,
            getUrl: null,
            onLoaded: null,
            serverSort: true,
            serverFilter: true,
            clientPaging: false,
            filterDebounceMs: 350
        }, options || {});

        this.currentPage = 1;
        this.pageSize = this.options.pageSize;
        this.totalCount = 0;
        this.sortCol = null;
        this.sortDir = null;
        this.filterParams = {};
        this.isLoading = false;
        this.filterTimer = null;

        this._initContainer();
        this._bindGridEvents();
        this._renderControls();

        if (this.options.clientPaging) {
            this.totalCount = this.grid.getRowsNum ? this.grid.getRowsNum() : 0;
            this._updateNav();
        }
    }

    CnsGridPagination.prototype._initContainer = function () {
        var container;
        if (this.options.containerId) {
            container = $('#' + this.options.containerId);
        }

        if (!container || !container.length) {
            var gridBoxId = this.options.gridContainerId || (this.grid.entBox ? this.grid.entBox.id : null);
            var autoId = (gridBoxId ? gridBoxId : 'cns-grid') + '-pagination-bar';

            container = $('#' + autoId);
            if (!container.length) {
                container = $('<div id="' + autoId + '" class="cns-grid-pagination-container"></div>');
                if (gridBoxId && $('#' + gridBoxId).length) {
                    $('#' + gridBoxId).after(container);
                } else if (this.grid.entBox) {
                    $(this.grid.entBox).after(container);
                }
            }
        }

        this.$container = container;
    };

    CnsGridPagination.prototype._renderControls = function () {
        var self = this;
        var optionsHtml = '';
        var sizes = self.options.pageSizeOptions || [10, 25, 50, 100];
        for (var i = 0; i < sizes.length; i++) {
            var sz = sizes[i];
            optionsHtml += '<option value="' + sz + '"' + (sz === self.pageSize ? ' selected' : '') + '>' + sz + '</option>';
        }

        var html = '' +
            '<div class="cns-pagination-bar d-flex flex-wrap justify-content-between align-items-center mt-2 mb-2 p-2 bg-light border rounded" style="font-size: 13px;">' +
            '  <div class="cns-page-size d-flex align-items-center my-1">' +
            '    <span class="mr-2 text-muted">Tampilkan</span>' +
            '    <select class="form-control form-control-sm cns-page-size-select" style="width: 75px; display: inline-block;">' +
            optionsHtml +
            '    </select>' +
            '    <span class="ml-2 text-muted">baris per halaman</span>' +
            '  </div>' +
            '  <div class="cns-page-info text-muted my-1 font-weight-normal">' +
            '    Menampilkan <span class="cns-from-val font-weight-bold">0</span> sampai <span class="cns-to-val font-weight-bold">0</span> dari <span class="cns-total-val font-weight-bold">0</span> data' +
            '  </div>' +
            '  <div class="cns-page-nav my-1">' +
            '    <ul class="pagination pagination-sm mb-0">' +
            '    </ul>' +
            '  </div>' +
            '</div>';

        self.$container.html(html);

        self.$container.find('.cns-page-size-select').on('change', function () {
            var newSize = parseInt($(this).val(), 10);
            self.setPageSize(newSize);
        });

        self._updateNav();
    };

    CnsGridPagination.prototype._updateNav = function () {
        var self = this;
        var total = self.totalCount;
        var pageSize = self.pageSize;
        var totalPages = Math.max(1, Math.ceil(total / pageSize));

        if (self.currentPage > totalPages) {
            self.currentPage = totalPages;
        }

        var from = total === 0 ? 0 : (self.currentPage - 1) * pageSize + 1;
        var to = total === 0 ? 0 : Math.min(self.currentPage * pageSize, total);

        self.$container.find('.cns-from-val').text(from.toLocaleString('id-ID'));
        self.$container.find('.cns-to-val').text(to.toLocaleString('id-ID'));
        self.$container.find('.cns-total-val').text(total.toLocaleString('id-ID'));

        var $nav = self.$container.find('.cns-page-nav ul.pagination');
        $nav.empty();

        // First & Prev buttons
        var isFirstDisabled = (self.currentPage <= 1);
        $nav.append(
            '<li class="page-item ' + (isFirstDisabled ? 'disabled' : '') + '">' +
            '  <a class="page-link" href="#" data-page="1" title="Halaman Pertama">&laquo;</a>' +
            '</li>'
        );
        $nav.append(
            '<li class="page-item ' + (isFirstDisabled ? 'disabled' : '') + '">' +
            '  <a class="page-link" href="#" data-page="' + (self.currentPage - 1) + '" title="Sebelumnya">&lsaquo;</a>' +
            '</li>'
        );

        // Page number buttons (sliding window of 5 pages)
        var maxButtons = 5;
        var startPage = Math.max(1, self.currentPage - Math.floor(maxButtons / 2));
        var endPage = Math.min(totalPages, startPage + maxButtons - 1);
        if (endPage - startPage + 1 < maxButtons) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }

        if (startPage > 1) {
            $nav.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }

        for (var p = startPage; p <= endPage; p++) {
            var isActive = (p === self.currentPage);
            $nav.append(
                '<li class="page-item ' + (isActive ? 'active' : '') + '">' +
                '  <a class="page-link" href="#" data-page="' + p + '">' + p + '</a>' +
                '</li>'
            );
        }

        if (endPage < totalPages) {
            $nav.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }

        // Next & Last buttons
        var isLastDisabled = (self.currentPage >= totalPages);
        $nav.append(
            '<li class="page-item ' + (isLastDisabled ? 'disabled' : '') + '">' +
            '  <a class="page-link" href="#" data-page="' + (self.currentPage + 1) + '" title="Berikutnya">&rsaquo;</a>' +
            '</li>'
        );
        $nav.append(
            '<li class="page-item ' + (isLastDisabled ? 'disabled' : '') + '">' +
            '  <a class="page-link" href="#" data-page="' + totalPages + '" title="Halaman Terakhir">&raquo;</a>' +
            '</li>'
        );

        $nav.find('a.page-link').on('click', function (e) {
            e.preventDefault();
            var targetPage = parseInt($(this).data('page'), 10);
            if (!isNaN(targetPage) && targetPage >= 1 && targetPage <= totalPages && targetPage !== self.currentPage) {
                self.setPage(targetPage);
            }
        });
    };

    CnsGridPagination.prototype._bindGridEvents = function () {
        var self = this;

        // Server-side sorting
        if (self.options.serverSort && typeof self.grid.attachEvent === 'function') {
            self.grid.attachEvent('onBeforeSorting', function (ind, type, direction) {
                self.sortCol = ind;
                self.sortDir = direction; // 'asc' or 'des' (dhtmlx passes 'asc'/'des')
                if (self.sortDir === 'des') self.sortDir = 'desc';

                self.currentPage = 1;
                self.reload(true, function () {
                    if (typeof self.grid.setSortImgState === 'function') {
                        self.grid.setSortImgState(true, ind, direction);
                    }
                });
                return false; // prevent client-side sort
            });
        }

        // Server-side filtering
        if (self.options.serverFilter && typeof self.grid.attachEvent === 'function') {
            self.grid.attachEvent('onFilterStart', function (colIndexes, values) {
                if (self.filterTimer) {
                    clearTimeout(self.filterTimer);
                }

                self.filterTimer = setTimeout(function () {
                    var filters = {};
                    for (var i = 0; i < colIndexes.length; i++) {
                        var val = values[i];
                        if (val !== undefined && val !== null && String(val).trim() !== '') {
                            filters[colIndexes[i]] = String(val).trim();
                        }
                    }
                    self.filterParams = filters;
                    self.currentPage = 1;
                    self.reload(true);
                }, self.options.filterDebounceMs);

                return false; // prevent client-side filter
            });
        }

        // On XML Load End: extract total_count and update navigation
        if (typeof self.grid.attachEvent === 'function') {
            self.grid.attachEvent('onXLE', function (gridObj, count, id, xmlDoc) {
                self.isLoading = false;
                try {
                    var total = 0;
                    if (xmlDoc && xmlDoc.responseXML) {
                        var root = xmlDoc.responseXML.documentElement;
                        if (root && root.getAttribute) {
                            var tc = root.getAttribute('total_count');
                            if (tc !== null && tc !== undefined && tc !== '') {
                                total = parseInt(tc, 10);
                            }
                        }
                    }
                    if (isNaN(total) || total <= 0) {
                        total = self.grid.getRowsNum ? self.grid.getRowsNum() : 0;
                    }
                    self.totalCount = total;
                } catch (e) {
                    self.totalCount = self.grid.getRowsNum ? self.grid.getRowsNum() : 0;
                }

                self._updateNav();

                if (typeof self.options.onLoaded === 'function') {
                    self.options.onLoaded(self, xmlDoc);
                }
            });
        }

        // Client-side paging sync (DHTMLX enablePaging)
        if (self.options.clientPaging && typeof self.grid.attachEvent === 'function') {
            var syncClientPaging = function () {
                self.totalCount = self.grid.getRowsNum ? self.grid.getRowsNum() : 0;
                self.currentPage = self.grid.currentPage || 1;
                self._updateNav();
            };
            self.grid.attachEvent('onPageChanged', syncClientPaging);
            self.grid.attachEvent('onFilterEnd', syncClientPaging);
        }
    };

    CnsGridPagination.prototype.buildUrl = function () {
        var self = this;
        var posStart = (self.currentPage - 1) * self.pageSize;
        var count = self.pageSize;

        if (typeof self.options.getUrl === 'function') {
            return self.options.getUrl({
                posStart: posStart,
                count: count,
                page: self.currentPage,
                pageSize: self.pageSize,
                sortCol: self.sortCol,
                sortDir: self.sortDir,
                filters: self.filterParams
            });
        }

        return '';
    };

    CnsGridPagination.prototype.reload = function (resetToPageOne, callback) {
        var self = this;
        if (resetToPageOne) {
            self.currentPage = 1;
        }

        if (self.options.clientPaging) {
            if (typeof self.grid.changePage === 'function') {
                self.grid.changePage(self.currentPage);
            }
            self.totalCount = self.grid.getRowsNum ? self.grid.getRowsNum() : 0;
            self._updateNav();
            if (typeof callback === 'function') {
                callback();
            }
            return;
        }

        var url = self.buildUrl();
        if (!url) return;

        self.isLoading = true;
        self.grid.clearAll();
        self.grid.load(url, function () {
            self.isLoading = false;
            if (typeof callback === 'function') {
                callback();
            }
            if (self.grid && typeof self.grid.setSizes === 'function') {
                self.grid.setSizes();
            }
        });
    };

    CnsGridPagination.prototype.setPage = function (page) {
        var self = this;
        self.currentPage = Math.max(1, parseInt(page, 10));
        self.reload(false);
    };

    CnsGridPagination.prototype.setPageSize = function (size) {
        var self = this;
        self.pageSize = parseInt(size, 10) || 25;
        if (self.options.clientPaging && self.grid) {
            self.grid.rowsBufferOutSize = self.pageSize;
        }
        self.currentPage = 1;
        self.reload(true);
    };

    window.initCnsGridPagination = function (grid, options) {
        return new CnsGridPagination(grid, options);
    };

})(window, jQuery);
