jQuery(function ($) {
    if (!mh.hasOwnProperty('tenders')) mh.tenders = {};
    _.extend(mh.tenders, {
        packages: {
            data: {
                vars: null, // Populated by hashChange
                
                categories: [],

                current: {
                    category: null,
                    package: null
                },

                updateQueue: []
            },

            init: function () {
                //$('.mh-product-wrapper .mh-loading').hide();
                var self = this;
                self.refreshData();
                
                $(window).bind('hashchange', function(e) {
                    $('.mh-products .mh-card').fadeOut(300);
                    $('html, body').animate({ scrollTop: 0 }, 300);

                    self.updateVars();
                    //console.log(window.location.hash, self.data.current.category);
                    setTimeout(function() {
                        self.drawCategory(); // self.refreshCategory();
                        $('.mh-products .mh-card').fadeIn(300);
                    }, 300);
                });

                $(window).bind('beforeunload', function() {
                    if(self.data.updateQueue.length) 
                        return 'You have unsaved changes';
                });
            },

            refreshData: function() { // console.log(this.data.tender.tenderid);
                var self = this;
                $('[data-loading-categories]').show();
                $('[data-loading-packages]').show();
                
                $.get({
                    url: get_if_exist(mh, 'urls.api') + 'clickhome.myhome/V2/tenders/' + self.data.tender.tenderid + '/packages/primaryCategories',
                    headers: mh.auth,
                    dataType: 'json',
                    contentType: 'application/json'
                }).success(function(response) { // console.log('primaryCategories', response);
                    self.data.categories = response;
                    self.updateVars();
                    self.drawCategories();
                    self.drawCategory(); // self.refreshCategory();
                    if(self.data.categories.length) self.calcTotalQuantities();
                    $('[data-loading-categories]').hide();
                    $('[data-loading-packages]').hide();
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
                self.data.vars.cat = get_if_exist(self.data.current.category, 'primaryCategoryId');

                self.data.current.subCategory = !self.data.current.category ? null : (function() {
                    if(self.data.vars.subCat)
                        return _.find(self.data.current.category.subCategories, {id: +self.data.vars.subCat});
                    else
                        return self.data.current.category.subCategories[0];
                }());
                self.data.vars.subCat = get_if_exist(self.data.current.subCategory, 'id');
            },

            drawCategories: function() {
                var self = this;
                var $categories = $('[data-categories]');
                _.each(self.data.categories, function(category) { // console.log(category.name, category.packages);
                    var $li = $('<li>').attr('data-category-id', category.primaryCategoryId);
                    var $a = $('<a>')//.attr('href', '#cat=' + category.id)
                        .on('click', function() { // console.log(this);
                            // $(this).closest('ul').find('li').removeClass('mh-active');
                            $(this).closest('li').toggleClass('mh-active'); // addClass('mh-active');
                        }
                    );
                    var $small = $('<small>').text(category.selectedCount);
                    $li.append($a.text(category.name || '').append($small));//.prepend($small));
                    
                    if(category.selectedCount <= 0) $small.hide();
                    //if(category.outStandingCount <= 0) $li.addClass('mh-done');
                    
                    // Active Primary Category
                    if(category.primaryCategoryId == self.data.current.category.primaryCategoryId) $li.addClass('mh-active');

                    // Sub-Category
                    var $subCat = $('<ul>').addClass('sub-categories');
                    _.each(category.subCategories, function(subCat) {
                        var $subCatLi = $('<li>').attr('data-sub-category-id', subCat.id).append($('<a>')
                            .attr('href', '#cat=' + category.primaryCategoryId + '&subCat=' + subCat.id)
                            .text(subCat.name || '')
                            .on('click', function() { // console.log(this);
                                $categories.find('ul li.mh-active').removeClass('mh-active');
                                $(this).closest('li').addClass('mh-active');
                            })
                        );
                        if(+self.data.vars.subCat == subCat.id) $subCatLi.addClass('mh-active');
                        $small = $('<small>').text(subCat.selectedCount);
                        $subCatLi.find('a').append($small);
                        if(subCat.selectedCount <= 0) $small.hide();
                        //if(subCat.outStandingCount <= 0) $subCatLi.addClass('mh-done');

                        $subCat.append($subCatLi); //.append($placeholders));
                    });

                    $categories.append($li.append($subCat)); // $categories.append($li);
                });
            },

            drawCategory: function() {
                var self = this;
                if(!self.data.current.subCategory) {
                    $('[data-no-results]').show();
                    return;
                }

                var $packages = $('[data-packages] > .mh-products-' + (self.data.current.subCategory.viewTypeCode == 'L' ? 'list' : 'grid'));
                var $basePackage = $packages.find('[data-base="package-' + (self.data.current.subCategory.viewTypeCode == 'L' ? 'list' : 'grid') + '"]');
                $('[data-packages] .mh-product-wrapper:not([data-base])').remove();

                // Title
                $('.mh-products-header').find('[data-category-name]').text(self.data.current.subCategory.name || '');
                if(self.data.current.subCategory.description)
                    $('.mh-products-header').find('[data-category-description]').text(self.data.current.subCategory.description || '');
                else 
                    $('.mh-products-header').find('[data-category-description]').hide();

                // Packages
                if(self.data.current.subCategory.packages.length) {
                    _.each(self.data.current.subCategory.packages, function(package) { // console.log(package);
                        var $package = $basePackage.clone().removeAttr('data-base');

                        $package.find('[data-package-id]').attr({
                            'id': 'package-' + package.id,
                            'data-package-id': package.id,
                            'checked': package.selected, //>0 ? true : false,
                            'disabled': !self.data.tender.isPackagesClientEditable                        
                        });

                        if(self.options.showItemPrices) $package.find('[data-price]').text((package.sellPrice || 0).formatMoney('$', 2, '.', ',')).show();
                        else $package.find('[data-price]').hide();

                        $package.find('[data-select]').attr('for', 'package-' + package.id);
                        $package.find('[data-name]').text(package.name || '');
                        $package.find('[data-description]').text(package.description || '');

                        if(self.data.current.subCategory.viewTypeCode == 'I') {
                            var photoUrl = package.thumbnailUrl || get_if_exist(package, 'imageUrls[0]');
                            photoUrl = photoUrl ? mh.urls.api + photoUrl : mh.urls.images + '/noPhoto.gif';
                            $package.find('[data-photo]').attr('src', photoUrl).addClass(package.thumbnailUrl ? 'cover' : 'contain');
                            
                            if(package.imageUrls.length) $package.find('[data-photo-count] span').text(package.imageUrls.length);
                            else $package.find('[data-photo-count]').remove(); //removeAttr('class').empty();
                        } else $package.find('.mh-img').remove();

                        if(package.isPalette) $package.find('.mh-top .mh-option-type.mh-ispalette').show();
                        //$package.find('[data-note]').val(package.clientNotes || '');

                        $package.find('[data-open-modal]').on('click', function() { //console.log(option);
                            self.detailsModal.open(package); //mh.tenders.selectionsEdit.detailsModal.open();
                        });
                        
                        $packages.append($package);
                    });
                }
                //$('ul.mh-product-categories li.mh-active').removeClass('mh-active');
                //$('ul.mh-product-categories li[data-category=' + self.data.category.primaryCategoryId + ']').addClass('mh-active');
                
                setTimeout(function() {
                    $(window).trigger('content-loaded');
                });
            },

            sync: function () { // console.log('mh.tenders.packages.sync()', self);
                $el = $(mh.events.getTarget());
                $wrapper = $el.closest('.mh-product-wrapper');
                $chkBox = $wrapper.find('.mh-checkbox');

                packageId = parseInt($chkBox.attr('data-package-id')); // console.log('packageId', packageId);

                // Sync original values (if viewing modal)
                $modal = $el.closest('#colorbox');
                if ($modal.length) {
                    $orig = $('.page .mh-products [data-package-id=' + packageId + ']').closest('.mh-product-wrapper'); //$modal.find('[data-id]').val());
                    //console.log('isModal', $orig);
                    $orig.find('.mh-checkbox').prop('checked', $chkBox.prop('checked'));
                    //$orig.find('.mh-quantity-input').val($qty.val());
                }

                // Sync Model
                self.data.current.package = _.findWhere(self.data.current.subCategory.packages, { id: packageId }); console.log('current.package', self.data.current.package);
                if (!self.data.current.package.allowOtherPackages) { // console.log('Dont allow other packages');
                    self.data.current.category.packages = _.each(self.data.current.subCategory.packages, function (el) {
                        el.selected = (el.id == self.data.current.package.id) ? $chkBox.prop('checked') ? true : false : false;
                    });
                    $('.page .mh-products-body .mh-product-wrapper input.mh-checkbox:not([data-package-id=' + packageId + '])').prop('checked', false);
                } else self.data.current.package.selected = $chkBox.prop('checked') ? true : false;

                // Don't sync if invalid data
                if (packageId == 'NaN') {
                    console.warn('Invalid package ID');
                    return;
                } //else console.log('Syncing...', self.data.current.package);
                self.calcTotalQuantities();

                // Update Package in updateQueue
                var update = {
                    tenderPackageId: self.data.current.package.id,
                    selected: self.data.current.package.selected
                };
                var existingPackage = _.findWhere(self.data.updateQueue, {tenderPackageId: self.data.current.package.id});
                if(existingPackage)
                    _.extend(existingPackage, update);
                else
                    self.data.updateQueue.push(update);

                $('.mh-sticky-footer #save-changes').attr('disabled', false).find('.mh-button').text('Save ' + self.data.updateQueue.length + ' Change' + (self.data.updateQueue.length>1?'s':''));
            },

            syncTimeout: false,

            save: function() { console.log('save', this.data.updateQueue);
                $('.mh-products .mh-product-wrapper .mh-loading').show();
                $('.mh-sticky-footer #save-changes').attr('disabled', true).find('.mh-button').text('Saving...');

                $.ajax({
                    type: 'PUT',
                    url: get_if_exist(mh, 'urls.api') + 'clickhome.myhome/V2/' + 'tenders/' + self.data.tender.tenderid + '/packages',
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

            calcTotalQuantities: function () { // console.log('calcTotalQuantities()');
                var self = this;

                // SubCategory
                self.data.current.subCategory.selectedCount = _.reduce(self.data.current.subCategory.packages, function (memo, package) { // console.log(memo, package);
                    return memo + (package.selected ? 1 : 0);
                }, 0); // console.log('selectedCount', self.data.current.category.selectedCount);
                //self.data.current.subCategory.outStandingCount = self.data.current.category.totalCount - self.data.current.category.selectedCount; 
                var $subCategoryBadge = $('.mh-product-categories li[data-sub-category-id=' + self.data.current.subCategory.id + '] > a > small');
                if (self.data.current.subCategory.selectedCount > 0) $subCategoryBadge.text(self.data.current.subCategory.selectedCount).show();
                else $subCategoryBadge.hide();

                // Category
                self.data.current.category.selectedCount = _.reduce(self.data.current.category.subCategories, function (memo, category) { // console.log(memo, +el.value);
                    return memo + category.selectedCount;
                }, 0); 
                var $categoryBadge = $('.mh-product-categories li[data-category-id=' + self.data.current.category.primaryCategoryId + '] > a > small');
                if (self.data.current.category.selectedCount > 0) $categoryBadge.text(self.data.current.category.selectedCount).show();
                else $categoryBadge.hide();
            },

            detailsModal: {
                $el: $('#mh-package-details'),

                open: function (package) { // console.log('detailsModal.open', package);
                    //var selectedCategory = _.findWhere(self.categories, { Id: categoryId }); console.log('selectedCategory', selectedCategory);
                    self.data.current.package = _.findWhere(self.data.current.subCategory.packages, { id: package.id }); console.log('current.package', self.data.current.package);

                    // Refresh selection data
                   // this.$el.find('[data-id]').val(self.data.current.package.id);
                    this.$el.find('[data-title]').text(self.data.current.package.name || '');
                    this.$el.find('[data-description]').text(self.data.current.package.description || '');
                    this.$el.find('[data-price]').text((self.data.current.package.sellPrice || 0).formatMoney('$', 2, '.', ','));
                    this.$el.find('[data-checkbox]').prop('checked', self.data.current.package.selected ? true : false).attr('data-package-id', self.data.current.package.id);
                    //this.$el.find('[data-quantity]').val(selection.count);
                    //console.log(this.$el.find('[data-id]').val(), selection.count);

                    // Build Slideshow
                    var $slideshow = this.$el.find('[data-slideshow-images]').empty();
                    if (self.data.current.package.imageUrls) {
                        _.each(self.data.current.package.imageUrls, function (el, i) { // console.log(el);
                            $slideshow.append($('<div><img src="' + mh.urls.api + el + '" /></div>'));
                            //$slideshow.append($('<div><img src="' + self.xhr.url.replace('ajax', 'post') + '/?myHomeDocumentId=' + el.docId + '&' + $.param(photoParams) + '" /></div>'));
                        });
                    } else {
                        $slideshow.append($('<div><div class="mh-no-photo"><i class="fa fa-picture-o"></i>No Photo<br/>Available</div></div>'));
                    }

                    // Toggle Slideshow
                    if (self.data.current.package.imageUrls.length > 0) this.$el.find('[data-slideshow-images]').parent().removeClass('mh-hide');
                    else this.$el.find('[data-slideshow-images]').parent().addClass('mh-hide');

                    // Toggle Price
                    if (self.data.current.package.sellPrice > 0) this.$el.find('.mh-price').removeClass('mh-hide');
                    else this.$el.find('.mh-price').addClass('mh-hide');

                    // Disable non-editable
                    this.$el.find('[data-checkbox]').attr('disabled', !self.data.tender.isPackagesClientEditable);

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
                        },
                        onCleanup: function () {
                            //delete self.data.currentcurrent.packagePackage;
                        }
                    })); //.on('afterChange', mh.colorbox.resize());
                },
            }
        }
    });
    var self = mh.tenders.packages;
});
