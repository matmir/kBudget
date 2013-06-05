$(function() {
	
	var transaction = {
			
			init: function() {
				
				$('#catWait').hide();
				
				this.initChangeMainCategory();
				this.initAddMainCategory();
				this.initChangeSubCategory();
				this.initAddSubCategory();
				
				// Check if there is some data from POST (controller injects data to the lists)
				if ($('#mainCategoryList').val() == 0) { // Add new
					
					$('.newMainCategoryRow').show();
					
				} else if ($('#mainCategoryList').val() == -1) { // Select
					
					$('.newMainCategoryRow').hide();
					
					$('.newSubCategoryRow').hide();
					$('.newSubCategoryEditRow').hide();
					
				} else { // Is main category selected
					
					$('.newMainCategoryRow').hide();
					$('.newSubCategoryRow').show();
					
				}
				if ($('#subCategoryList').val() == 0) { // Add new
					
					$('.newSubCategoryEditRow').show();
					
				} else {
					
					$('.newSubCategoryEditRow').hide();
					
				}
				
			},
			initChangeSubCategory: function() {
				
				// Change subcategory
				$('#subCategoryList').change(function(){
					
					// Check selection
					if ($('option:selected', this).val() == 0) { // Add new category
						
						$('.newSubCategoryEditRow').show();
						
					} else {
						
						$('.newSubCategoryEditRow').hide();
						
					}

			    });
				
			},
			initAddSubCategory: function() {
				
				// Click add subcategory
				$('#addSubCategory').click(function(){

					// Disable controlls
					transaction.disableControlls(true);
					
					// Get transaction type
					var transactionType = $('#transactionType').val();
					
					// Get parent category id
					var pcid = $('#mainCategoryList').val();
					
					// Read category name
					var catName = $('#newSubCategory').val();
					
					// Check if there is text
					if (catName != "") {
						
						// Add category
						transaction.addCategory(catName, pcid, transactionType);
						
					} else {
						
						alert('Podaj nazwę kategorii!');
						
						// Enable controlls
						transaction.disableControlls(false);
						
					}

			    });
				
			},
			initChangeMainCategory: function() {
				
				// Change parent category
				$('#mainCategoryList').change(function(){
					
					// Check selection
					if ($('option:selected', this).val() == 0) { // Add new category
						
						$('.newMainCategoryRow').show();
						
						$('.newSubCategoryRow').hide();
						$('.newSubCategoryEditRow').hide();
						
					} else if ($('option:selected', this).val() == -1) { // Select category...
						
						$('.newMainCategoryRow').hide();
						
						$('.newSubCategoryRow').hide();
						$('.newSubCategoryEditRow').hide();
						
					} else {
						
						$('.newMainCategoryRow').hide();
						$('.newSubCategoryRow').show();
						
						// Read main category id
						var cid = $('#mainCategoryList').val();
						
						// Read category type
						var ctype = $('#transactionType').val();
						
						transaction.disableControlls(true);
						
						// load subcategories
						transaction.loadSubCategories(cid, ctype);
					}

			    });
				
			},
			/**
			 *  Load sub categories
			 *  
			 *  @param int cid Category id
			 *  @param int ctype Category type (0 - income, 1 - expense)
			 */
			loadSubCategories: function (cid, ctype) {
				
				// Clear subcategories select
				$('#subCategoryList').find('option').remove().end();
				
				// Get subcategories
				$.ajax({
        			type: 'POST',
        		    url: '/user/category/get-categories',
        		    cache: false,
        		    data: {
                        'cid': cid,
                        'c_type': ctype
        		    },
        		    dataType: 'json'
        		}).done(function ( data ) {
        			  
        			// Check status
    		    	if (data.status == "OK") {
    		    		
    		    		// Add required entry
    		    		$('#subCategoryList').append('<option value="-1">Brak</option>');
    		    		$('#subCategoryList').append('<option value="0">Dodaj nową...</option>');
    		    		
    		    		// get all categories
    		    		$.each(data, function(key, value) {
    		    			
    		    			if (value != 'OK') {
    		    				
    		    				if (ctype == 0) {
    		    					
    		    					// Add to subcategories select
            		    			$('#subCategoryList').append('<option value="'+value+'">'+key+'</option>');
    		    					
    		    				} else {
    		    					
    		    					// Add to subcategories select
            		    			$('#subCategoryList').append('<option value="'+value+'">'+key+'</option>');
    		    					
    		    				}
    		    				
    		    			}
    		    			
    		    		});
    		    		
    		    	} else if (data.status == "noCategories") {
    		    		
    		    		// Add required entry
    		    		$('#subCategoryList').append('<option value="-1">Brak</option>');
    		    		$('#subCategoryList').append('<option value="0">Dodaj nową...</option>');
    		    		
    		    	} else if (data.status == "noPostData") {
    		    		
    		    		alert('Wystąpił błąd!');
    		    		
    		    	}
        			
    		    	transaction.disableControlls(false);
    		    	
				});
				
			},
			initAddMainCategory: function() {
				
				// Click add main category
				$('#addMainCategory').click(function(){

					// Disable controlls
					transaction.disableControlls(true);
					
					// Get transaction type
					var transactionType = $('#transactionType').val();
					
					// Read category name
					var catName = $('#newMainCategory').val();
					
					// Check if there is text
					if (catName != "") {
						
						// Add category
						transaction.addCategory(catName, null, transactionType);
						
					} else {
						
						alert('Podaj nazwę kategorii!');
						
						// Enable controlls
						transaction.disableControlls(false);
						
					}

			    });
				
			},
			/**
			 * Disable all controlls.
			 * 
			 * @param bool disable
			 */
			disableControlls: function(disable) {
				
				if (disable) {
					
					$('#catWait').show();
					
					$('#mainCategoryList').attr('disabled', 'disabled');
					$('#newMainCategory').attr('disabled', 'disabled');
					$('#addMainCategory').attr('disabled', 'disabled');
					
					$('#subCategoryList').attr('disabled', 'disabled');
					$('#newSubCategory').attr('disabled', 'disabled');
					$('#addSubCategory').attr('disabled', 'disabled');
					
					$('#t_date').attr('disabled', 'disabled');
					
					$('#t_content').attr('disabled', 'disabled');
					
					$('#t_value').attr('disabled', 'disabled');
					
					$('#submitbutton').attr('disabled', 'disabled');
					
				} else {
					
					$('#catWait').hide();
					
					$('#mainCategoryList').removeAttr('disabled');
					$('#newMainCategory').removeAttr('disabled');
					$('#addMainCategory').removeAttr('disabled');
					
					$('#subCategoryList').removeAttr('disabled');
					$('#newSubCategory').removeAttr('disabled');
					$('#addSubCategory').removeAttr('disabled');
					
					$('#t_date').removeAttr('disabled');
					
					$('#t_content').removeAttr('disabled');
					
					$('#t_value').removeAttr('disabled');
					
					$('#submitbutton').removeAttr('disabled');
					
				}
				
			},
			/**
			 * Add category. If pcid is given - add subcategory
			 * 
			 * @param int cid Category id
			 * @param string catName Category name
			 * @param int pcid Parent category id
			 * @param int catType Category type (0 - income, 1 - expense)
			 */
			addCategory: function(catName, pcid, catType) {
				
				// Add category
				$.ajax({
        			type: 'POST',
        		    url: '/user/category/save',
        		    cache: false,
        		    data: {
        		    	'cid': null,
        		    	'pcid': pcid,
                        'c_type': catType,
                        'c_name': catName,
        		    },
        		    dataType: 'json'
        		}).done(function ( data ) {
        			
        			// Check status
        			if (data.status == 'OK') {
        				
        				// Clear add edits
            			$('#newMainCategory').val('');
        				
            			// Main category?
    					if (pcid == null) { // Main
    						// Add to list
    						$('#mainCategoryList').append('<option value="'+data.cid+'">'+data.name+'</option>');
    						
    						// Select new category
    						$('#mainCategoryList').val(data.cid);
    						
    						// Trigger change event
    						$('#mainCategoryList').change();
    						
    					} else { // Subcategory
    						$('#subCategoryList').append('<option value="'+data.cid+'">'+data.name+'</option>');
    						
    						// Select new category
    						$('#subCategoryList').val(data.cid);
    						
    						// Trigger change event
    						$('#subCategoryList').change();
    						
    					}
        				
        			} else if (data.status == 'exists') {
        				
        				alert('Kategoria o podanej nazwie już istnieje!');
        				
        			} else if (data.status == 'badData') {
        				
        				alert('Przekazano niepoprawne dane!');
        				
        			} else if (data.status == 'noPostData') {
    		    		
    		    		alert('Wystąpił błąd!');
    		    		
    		    	}
        			
        			transaction.disableControlls(false);
        			
				});
				
			}
			
	};
	
	transaction.init();
	
});