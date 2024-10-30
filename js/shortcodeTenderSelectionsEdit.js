jQuery(function ($) { // console.log('tenderSelectionEdit.js');
    if (!mh.hasOwnProperty('tenders')) mh.tenders = {};
    _.extend(mh.tenders, {
        selectionsEdit: {

            data: {
                vars: null, // Populated by hashChange

                categories: null,
                areas: null,

                current: {
                    category: null,
                    subCategory: null,
                    placeholder: null,
                    option: null
                },

                updateQueue: []
            },

            init: function () {
                var self = this;//mh.tenders.selectionsEdit;
                self.getAreas();
                self.refreshData();

                $('[data-category-name]').text(get_if_exist(mh, 'tenders.selectionsEdit.data.tender.name'));
                
                $(window).bind('hashchange', function(e) { console.log(window.location.hash, e);
                    $('.mh-products .mh-card').fadeOut(300);
                    $('html, body').animate({ scrollTop: 0 }, 300);

                    self.updateVars();
                    setTimeout(function() {
                        self.drawCategory();
                        $('.mh-products .mh-card').fadeIn(300);
                    }, 300);
                });

                $(window).bind('beforeunload', function() {
                    if(self.data.updateQueue.length) 
                        return 'You have unsaved changes';
                });
            },

            getAreas: function() {
                var self = this;
                $.get({
                    url: get_if_exist(mh, 'urls.api') + 'clickhome.myhome/V2/tenders/' + self.data.tender.tenderid + '/areas', 
                    headers: mh.auth,
                    dataType: 'json',
                    contentType: 'application/json'
                }).success(function(response) { // console.log('areas', response);
                    self.data.areas = response;
                    var $select = $('select[data-area]');//.empty().append($placeholder);
                    _.each(self.data.areas, function(area) {
                        $select.append($('<option>').val(area.masterAreaId).text(area.masterArea));
                    });
                });
            },

            refreshData: function() { // console.log(this.data.tender.tenderid);
                var self = this;
                $('[data-loading-categories]').show();
                $('[data-loading-products]').show();
                
                $.get({
                    url: get_if_exist(mh, 'urls.api') + 'clickhome.myhome/V2/tenders/' + self.data.tender.tenderid + '/selections/primaryCategories',
                    headers: mh.auth,
                    dataType: 'json',
                    contentType: 'application/json'
                }).success(function(response) { // console.log('primaryCategories', response);
                    self.data.categories = response;
                    self.updateVars();
                    self.drawCategories();
                    self.drawCategory();
                    if(self.data.categories.length) self.calcTotalQuantities();
                    
                    $('[data-loading-categories]').hide();
                    $('[data-loading-products]').hide();
                });
            },

            updateVars: function() {
                var self = this;
                
                // Convert hashParams to object
                self.data.vars = _.reduce(window.location.hash.substr(1).split('&'), function(memo, keyVal) { 
                    keyVal = keyVal.split('=');
                    memo[keyVal[0]] = keyVal[1];
                    return memo; 
                }, {}); // console.log(self.data.vars);
                
                // Set current
                self.data.current.category = (function() {
                    if(self.data.vars.cat)
                        return _.find(self.data.categories, {primaryCategoryId: +self.data.vars.cat});
                    else
                        return self.data.categories[0];
                }());
                self.data.vars.cat = get_if_exist(self.data.current.category, 'categoryId');

                self.data.current.subCategory = !self.data.current.category ? null :(function() {
                    if(self.data.vars.subCat)
                        return _.find(self.data.current.category.subCategories, {optionCategoryId: +self.data.vars.subCat});
                    else
                        return self.data.current.category.subCategories[0];
                }());
                self.data.vars.subCat = get_if_exist(self.data.current.subCategory, 'optionCategoryId');

                self.data.current.placeholder = !self.data.current.subCategory ? null :(function() {
                    if(self.data.vars.placeholder)
                        return _.find(self.data.current.subCategory.selections, {placeholderSelectionId: +self.data.vars.placeholder});
                    else
                        return self.data.current.subCategory.selections[0];
                }());
                self.data.vars.placeholder = get_if_exist(self.data.current.placeholder, 'placeholderSelectionId');
            },

            drawCategories: function() {
                var self = this;
                var $categories = $('[data-categories]');
                _.each(self.data.categories, function(category) {
                    var $li = $('<li>').attr('data-category-id', category.primaryCategoryId);
                    var $a = $('<a>')//.attr('href', '#cat=' + category.primaryCategoryId)
                        .on('click', function() { // console.log(this);
                            $(this).closest('li').toggleClass('mh-active');
                        }
                    );
                    var $small = $('<small>').html('<i class="fa fa-exclamation"></i><i class="fa fa-check"></i>');
                    if(category.outStandingCount <= 0) $li.addClass('mh-done');
                    //$li.append($a.append($small).append($('<span>').text(category.name || '')));
                    $li.append($a.text(category.name || '').prepend($small));

                    // Active Primary Category
                    if(category.primaryCategoryId == self.data.current.category.primaryCategoryId) $li.addClass('mh-active');

                    // Sub-Category
                    var $subCat = $('<ul>').addClass('sub-categories');
                    _.each(category.subCategories, function(subCat) {
                        var $subCatLi = $('<li>').attr('data-sub-category-id', subCat.optionCategoryId).append($('<a>')
                            //.attr('href', '#cat=' + subCat.primaryCategoryId + '&subCat=' + subCat.optionCategoryId)
                            .text(subCat.categoryName || '')
                            .on('click', function() { // console.log(this);
                                $(this).closest('li').toggleClass('mh-active');
                            })
                        );
                        if(+self.data.vars.subCat == subCat.optionCategoryId) $subCatLi.addClass('mh-active');
                        $subCatLi.find('a').append($('<small>').text(subCat.outStandingCount));
                        if(subCat.outStandingCount <= 0) $subCatLi.addClass('mh-done');

                        // Placeholder
                        var $placeholders = $('<ul>').addClass('placeholders');
                        _.each(subCat.selections, function(placeholder) {
                            var $placeholderLi = $('<li>').attr('data-placeholder-id', placeholder.placeholderSelectionId).append($('<a>')
                                .attr('href', '#cat=' + subCat.primaryCategoryId + '&subCat=' + subCat.optionCategoryId + '&placeholder=' + placeholder.placeholderSelectionId)
                                .text(placeholder.placeholderName)
                                .on('click', function() { // console.log(this);
                                    $categories.find('ul.placeholders li.mh-active').removeClass('mh-active');
                                    $(this).closest('li').addClass('mh-active');
                                })
                            );
                            $placeholderLi.find('a').append($('<small>').text(placeholder.outStandingCount))
                            if(placeholder.outStandingCount <= 0) $placeholderLi.addClass('mh-done');

                            $placeholders.append($placeholderLi);
                            if(+self.data.vars.placeholder == placeholder.placeholderSelectionId) $placeholderLi.addClass('mh-active');
                        });

                        $subCat.append($subCatLi.append($placeholders));
                    });

                    $categories.append($li.append($subCat));
                });
            },

            drawCategory: function() {
                var self = this;
                if(!self.data.current.placeholder) {
                    $('[data-no-results]').show();
                    return;
                }

                var $products = $('[data-products]');
                var $baseProduct = $products.find('[data-base="product"]');
                $products.find('> .mh-product-wrapper:not([data-base])').remove();

                // Title
                $('.mh-products-header').find('[data-placeholder-name]').text(self.data.current.placeholder.placeholderName || '');
                if(self.data.current.placeholder.phDescription)
                    $('.mh-products-header').find('[data-placeholder-description]').text(self.data.current.placeholder.phDescription || '');
                else 
                    $('.mh-products-header').find('[data-placeholder-description]').hide();
                
                // Options
                if(self.data.current.placeholder.substitutionOptions.length) {
                    _.each(self.data.current.placeholder.substitutionOptions, function(option) { // console.log(option);
                        var $product = $baseProduct.clone().removeAttr('data-base');

                        $product.find('[data-option-id]').attr({
                            'id': 'option-' + option.optionId,
                            'data-option-id': option.optionId,
                            'checked': option.selectCount>0 ? true : false,
                            //'disabled': !self.data.tender.isSelectionsClientEditable                        
                        });
                        $product.find('[data-quantity]').val(option.selectCount);
                        if(!option.quantityReqd && self.options.showItemQuantities) $product.find('.mh-quantity').hide();

                        if(self.options.showItemPrices) $product.find('[data-price]').text(option.upgradePrice ? (option.upgradePrice || 0).formatMoney('$', 2, '.', ',') : '').show();
                        else $product.find('[data-price]').hide();

                        $product.find('[data-select]').attr('for', 'option-' + option.optionId);
                        $product.find('[data-name]').text(option.optionName || '');
                        $product.find('[data-description]').text(option.optionDescription || '');

                        var photoUrl = option.thumbnail || get_if_exist(option, 'documents[0].url');
                        photoUrl = photoUrl ? mh.urls.api + photoUrl : mh.urls.images + '/noPhoto.gif';
                        $product.find('[data-photo]').attr('src', photoUrl).addClass(option.thumbnail ? 'cover' : 'contain');
                        
                        if(option.documents) $product.find('[data-photo-count] span').text(option.documents.length);
                        else $product.find('[data-photo-count]').remove(); //removeAttr('class').empty();

                        if(option.upgrade) $product.find('.mh-top .mh-option-type.mh-isupgrade').show();
                        else $product.find('.mh-top .mh-option-type.mh-isalternate').show();

                        $product.find('[data-note]').val(option.clientComment || '');
                        $product.find('[data-area] option[value=' + option.masterAreaId + ']').prop('selected', true);
                        $product.find('[data-area-note]').val(option.allocationComment || '');

                        // Disable
                        $product.find('[data-option-id], [data-quantity], [data-note], [data-area], [data-area-note], .mh-button').attr('disabled', !self.data.tender.isSelectionsClientEditable);

                        // Events
                        $product.find('[data-open-modal]').on('click', function() { //console.log(option);
                            self.detailsModal.open(option); //mh.tenders.selectionsEdit.detailsModal.open();
                        });
                        
                        $products.append($product);
                    });
                } 

                //$('ul.mh-product-categories li.mh-active').removeClass('mh-active');
                //$('ul.mh-product-categories li[data-category=' + self.data.category.primaryCategoryId + ']').addClass('mh-active');
                
                setTimeout(function() {
                    $(window).trigger('content-loaded');
                });
            },

            sync: function () {//console.log(event.target);
                $el = $(mh.events.getTarget());
                $wrapper = $el.closest('.mh-product-wrapper'); //console.log('$wrapper', $wrapper);
                $chkBox = $wrapper.find('.mh-checkbox');
                //selectionId = parseInt($chkBox.data('placeholder-id')); console.log('selectionId', selectionId);
                productId = parseInt($chkBox.attr('data-option-id')); // console.log('productId', productId);

                self.data.current.option = _.findWhere(self.data.current.placeholder.substitutionOptions, { optionId: productId }); // console.log('currentOption', self.data.current.option);
                //self.data.current.option.selectCount = $chkBox.prop('checked') ? +$wrapper.find('[data-quantity]').val() || 1 : 0;
                self.data.current.option.clientComment = $wrapper.find('[data-note]').val() || null;
                self.data.current.option.allocationComment = $wrapper.find('[data-area-note]').val() || null;
                self.data.current.option.masterAreaId = +$wrapper.find('[data-area]').val() || null;

                // Sync checked/quantity values
                if ($el.is('.mh-checkbox')) {
                    self.data.current.option.selectCount = $chkBox.prop('checked') ? 1 : 0;
                    $wrapper.find('[data-quantity]').val(self.data.current.option.selectCount); //console.log('select', $qty);
                } else if ($el.is('[data-quantity]') || $el.is('.mh-quantity-input a')) { // console.log('quantity');
                    self.data.current.option.selectCount = +$wrapper.find('[data-quantity]').val();
                    $chkBox.prop('checked', self.data.current.option.selectCount > 0);
                }

                // Sync original values (if viewing modal)
                $modal = $el.closest('#colorbox');
                if ($modal.length) { // console.log('isModal');
                    $orig = $('.mh-wrapper-tender-selection-edit [data-option-id=' + productId + ']').closest('.mh-product-wrapper'); //$modal.find('[data-id]').val());
                    $orig.find('.mh-checkbox').prop('checked', $chkBox.prop('checked'));
                    $orig.find('.mh-quantity-input input').val(self.data.current.option.selectCount);
                }

                // Don't sync if invalid data
                if (productId == 'NaN') {
                    console.warn('Invalid product ID');
                    return;
                } //else console.log('Syncing...', self.data.current.option);
                self.calcTotalQuantities();

                // Update Placeholder in updateQueue
                var existingPlaceholder = _.findWhere(self.data.updateQueue, {placeholderSelectionId: self.data.current.placeholder.placeholderSelectionId});
                if(!existingPlaceholder) {
                    existingPlaceholder = {
                        placeholderSelectionId: self.data.current.placeholder.placeholderSelectionId,
                        substituteOptions: []
                    }
                    self.data.updateQueue.push(existingPlaceholder);
                }

                // Update Option within PlaceHolder within updateQueue
                var update = {
                    optionId: self.data.current.option.optionId,
                    quantity: self.data.current.option.selectCount,
                    clientComment: self.data.current.option.clientComment,
                    allocationComment: self.data.current.option.allocationComment,
                    masterAreaId: self.data.current.option.masterAreaId
                };
                var existingOption = _.findWhere(existingPlaceholder.substituteOptions, {optionId: self.data.current.option.optionId});
                if(existingOption)
                    _.extend(existingOption, update);
                else
                    existingPlaceholder.substituteOptions.push(update);
                
                $('.mh-sticky-footer #save-changes').attr('disabled', false).find('.mh-button').text('Save ' + self.data.updateQueue.length + ' Change' + (self.data.updateQueue.length>1?'s':''));
            },

            syncTimeout: false,

            save: function() { console.log('save', this.data.updateQueue);
                $('.mh-products .mh-product-wrapper .mh-loading').show();
                $('.mh-sticky-footer #save-changes').attr('disabled', true).find('.mh-button').text('Saving...');

                $.ajax({
                    type: 'PUT',
                    url: get_if_exist(mh, 'urls.api') + 'clickhome.myhome/V2/' + 'tenders/' + self.data.tender.tenderid + '/selections',
                    headers: mh.auth,
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(self.data.updateQueue)
                }).always(function(response) {
                    $('.mh-products .mh-loading').hide();
                }).success(function(response) { console.log('response', response);
                    //self.calcTotalQuantities();
                    self.data.updateQueue = [];
                    $('.mh-sticky-footer #save-changes .mh-button').text('Save Changes');
                    toastr['success']('Selection Successfully Updated');
                });
            },

            adjustQuantityBy: function (amount) { //console.log('adjustQuantityBy()');
                var $input = $(mh.events.getTarget()).closest('.mh-quantity-input').find('input');
                $input.val(parseInt($input.val()) + amount);
                $input.trigger('change');
            },

            toggleNote: function (selectionId) { // console.log('note.toggleNote()', selectionId);
                $(mh.events.getTarget()).closest('.mh-product-wrapper').toggleClass('flipped');
            },

            calcTotalQuantities: function () { // console.log('calcTotalQuantities()');
                var self = this;
                var totalRemainInt = 0;

                // Placeholder
                self.data.current.placeholder.selectedCount = _.reduce($('.mh-products .mh-card .mh-product-wrapper > .mh-checkbox:checked + .mh-product [data-quantity]'), function (memo, el) { // console.log(memo, +el.value);
                    return memo + parseInt(el.value);
                }, 0);
                self.data.current.placeholder.outStandingCount = self.data.current.placeholder.totalCount - self.data.current.placeholder.selectedCount; 
                var $placeholderBadge = $('.mh-product-categories .sub-categories .placeholders li[data-placeholder-id=' + self.data.current.placeholder.placeholderSelectionId + '] > a > small');
                if (self.data.current.placeholder.outStandingCount > 0) $placeholderBadge.text(self.data.current.placeholder.outStandingCount).closest('li').removeClass('mh-done');
                else $placeholderBadge.closest('li').addClass('mh-done');
                
                // SubCategory
                self.data.current.subCategory.selectedCount = _.reduce(self.data.current.subCategory.selections, function (memo, subCat) { // console.log(memo, +el.value);
                    return memo + subCat.selectedCount;
                }, 0);
                self.data.current.subCategory.outStandingCount = self.data.current.subCategory.totalCount - self.data.current.subCategory.selectedCount; 
                var $subCatBadge = $('.mh-product-categories .sub-categories li[data-sub-category-id=' + self.data.current.subCategory.optionCategoryId + '] > a > small');
                if (self.data.current.subCategory.outStandingCount > 0) $subCatBadge.text(self.data.current.subCategory.outStandingCount).closest('li').removeClass('mh-done');
                else $subCatBadge.closest('li').addClass('mh-done');
                
                // Category
                self.data.current.category.selectedCount = _.reduce(self.data.current.category.subCategories, function (memo, category) { // console.log(memo, +el.value);
                    return memo + category.selectedCount;
                }, 0); 
                self.data.current.category.outStandingCount = self.data.current.category.totalCount - self.data.current.category.selectedCount; 
                var $categoryBadge = $('.mh-product-categories li[data-category-id=' + self.data.current.category.primaryCategoryId + '] > a > small');
                if (self.data.current.category.outStandingCount > 0) $categoryBadge.html('<i class="fa fa-exclamation"></i>').closest('li').removeClass('mh-done');
                else $categoryBadge.html('<i class="fa fa-check"></i>').closest('li').addClass('mh-done');


                // Sticky Footer
                var $stickyFooter = $('.mh-products').find('.mh-sticky-footer');
                if(self.options.showRunningQuantities || self.options.showRunningPrices) $stickyFooter.show();
                else $stickyFooter.hide();

                if(!self.data.tender.isSelectionsClientEditable) {
                    $stickyFooter.find('.mh-selections-remain').parent().hide();
                    $stickyFooter.find('.mh-selections-complete').parent().hide();
                } else if (self.data.current.placeholder.outStandingCount > 0) {
                    $stickyFooter.find('[data-quantity-selected]').text(self.data.current.placeholder.selectedCount);
                    $stickyFooter.find('[data-quantity-total]').text(self.data.current.placeholder.totalCount);
                    $stickyFooter.find('[data-quantity-remain]').text(self.data.current.placeholder.outStandingCount);
                    if(self.options.showRunningQuantities) {
                        $stickyFooter.find('.mh-selections-complete').parent().hide();
                        $stickyFooter.find('.mh-selections-remain').parent().show();
                    }
                } else {
                    if (self.data.current.placeholder.outStandingCount == 0)
                        $stickyFooter.find('.mh-quantity-extra').hide();
                    else
                        $stickyFooter.find('.mh-quantity-extra').text('+' + (-self.data.current.placeholder.outStandingCount) + ' extra').show();
                    if(self.options.showRunningQuantities) {
                        $stickyFooter.find('.mh-selections-complete').parent().show();
                        $stickyFooter.find('.mh-selections-remain').parent().hide();
                    }
                }

                if(self.options.showRunningPrices) {
                    $stickyFooter.find('[data-running-price]').text('').closest('.mh-running-price').show();
                } else {
                    $stickyFooter.find('.mh-running-price').hide();
                }

                totalRemainInt += self.data.current.placeholder.outStandingCount > 0 ? self.data.current.placeholder.outStandingCount : 0; // Don't include negatives
            },

            detailsModal: {
                $el: $('#mh-selection-details'),

                open: function (option) { console.log('detailsModal.open', option);
                    if ($(mh.events.getTarget()).closest('.mh-icons').length) return;

                    //var activeCategory = _.findWhere(self.selections, { id: categoryId }); console.log('activeCategory', activeCategory);
                    //self.data.current.placeholder = _.findWhere(self.data.current.subCategory.selections, { placeholderId: selectionId }); //console.log('currentSelection', self.data.currentSelection);
                    self.data.current.option = option; //_.findWhere(self.data.current.placeholder.substitutionOptions, { optionId: productId }); //console.log('currentOption', self.data.currentOption);

                    // Refresh selection data
                    //this.$el.find('[data-id]').val(productId); //self.data.currentOption.optionId);
                    //this.$el.find('[data-placeholder-id]').val(selectionId);
                    this.$el.find('[data-title]').text(self.data.current.option.optionName);
                    this.$el.find('[data-description]').text(self.data.current.option.optionDescription || '');
                    this.$el.find('[data-note]').val(self.data.current.option.clientComment || '');
                    if(self.data.current.option.masterAreaId)
                        this.$el.find('[data-area] option[value=' + self.data.current.option.masterAreaId + ']').prop('selected', true);
                    else
                        this.$el.find('[data-area] option:first-child').prop('selected', true);
                    this.$el.find('[data-area-note]').val(self.data.current.option.allocationComment); //.text(self.data.current.option.masterAreaId || '');
                    
                    // Toggle Price
                    if (self.options.showItemPrices && self.data.current.option.upgradePrice) 
                        this.$el.find('.mh-price').show().find('[data-price]').text((self.data.current.option.upgradePrice || 0).formatMoney('$', 2, '.', ','));
                    else
                        this.$el.find('.mh-price').hide();

                    // Toggle Quantity
                    if (self.options.showItemQuantities && self.data.current.option.quantityReqd) this.$el.find('.mh-quantity').show();
                    else this.$el.find('.mh-quantity').hide();
                    
                   // this.$el.find('[data-select]').attr('for', 'option-' + self.data.current.option.optionId);
                    this.$el.find('[data-checkbox]').prop('checked', self.data.current.option.selectCount ? true : false).attr('data-option-id', option.optionId); // .attr('data-placeholder-id', selectionId);
                    this.$el.find('[data-quantity]').val(self.data.current.option.selectCount);
                    //console.log(this.$el.find('[data-id]').val(), self.data.currentSelection.selectCount);

                    // Build Slideshow
                    var $slideshow = this.$el.find('[data-slideshow-images]').empty();
                    if (self.data.current.option.documents) {
                        /*var photoParams = _.findWhere(self.xhr.actions, { myHomeAction: 'systemDocument' }); //console.log('photoParams', photoParams);
                        if (!photoParams) console.error('No params for MyHomeAction \'document\' loaded into js');
                        _.extend(photoParams, {
                            myHomeInline: 1,
                            myHomeThumb: 0,
                            myHomeCache: 0
                        });*/  //console.log($.param(photoParams));
                        _.each(self.data.current.option.documents, function (el, i) { console.log(el);
                            $slideshow.append($('<div><img src="' + mh.urls.api + el.url + '" /></div>'));
                            //$slideshow.append($('<div><img src="' + self.xhr.url.replace('ajax', 'post') + '/?myHomeDocumentId=' + el.docId + '&' + $.param(photoParams) + '" /></div>'));
                        });
                    } else {
                        $slideshow.append($('<div><div class="mh-no-photo"><i class="fa fa-picture-o"></i>No Photo<br/>Available</div></div>'));
                    }

                    // Toggle Slideshow
                    if (get_if_exist(self.data.current.option.documents, 'length') > 0) this.$el.find('[data-slideshow-images]').parent().removeClass('mh-hide');
                    else this.$el.find('[data-slideshow-images]').parent().addClass('mh-hide');

                    // Disable non-editable
                    this.$el.find('[data-checkbox], [data-quantity], [data-note], [data-area], [data-area-note], .mh-button').attr('disabled', !self.data.tender.isSelectionsClientEditable);
                    
                    // Open it
                    $.colorbox(_.extend({}, mh.colorbox.options, {
                        href: '#' + this.$el.attr('id'),
                        className: 'responsive',
                        onComplete: function () {
                            if ($slideshow.hasClass('slick-initialized')) {
                                $slideshow.slick('removeSlide', null, null, true);
                                $slideshow.slick('unslick');
                            }
                            $slideshow.slick({
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                arrows: true,
                                dots: true
                            });
                        }
                    }));
                },
            }
        }
    });

    var self = mh.tenders.selectionsEdit;
});