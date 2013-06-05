$(function() {
	
	var transaction = {
			
			init: function() {
				
				// Get number of transaction
				var trCount = $('#transactionImportForm #trCount').val();
				
				$('#catWait').hide();
				
				this.initStartValues(trCount);
				this.initChangeTransactionType(trCount);
				this.initChangeMainCategory(trCount);
				this.initAddMainCategory(trCount);
				this.initChangeSubCategory(trCount);
				this.initAddSubCategory(trCount);
			},
			initChangeTransactionType: function(trCount) {
				
				for (var i=0; i<trCount; i++) {
					
					// Change transaction type
					$('#t_type-'+i).change(function(){

						// Get nr of changed select
						var nr = this.name.split('-')[1];
						
						// Check selection
						if (($('option:selected', this).val() == 0)||($('option:selected', this).val() == 1)) { // Transaction
							$('#categoryRow-'+nr).show();
							$('#subCategoryRow-'+nr).hide();
							$('#newMainCategoryRow-'+nr).hide();
							$('#newSubCategoryRow-'+nr).hide();
							$('#accountRow-'+nr).hide();

							$('#catWait').show();
							
							// Load main categories
							transaction.loadCategories(null, this.value, nr);
							
						} else { // Transfer
							$('#categoryRow-'+nr).hide();
							$('#accountRow-'+nr).show();
						}

				    });
					
				}
				
			},
			initStartValues: function(trCount) {
				
				for (var i=0; i<trCount; i++) {
					
					// Check if selected transaction
					if ($('#t_type-'+i).val() == 0 || $('#t_type-'+i).val() == 1) {
						$('#categoryRow-'+i).show();
						$('#accountRow-'+i).hide();
					} else { // Selected transfer
						$('#categoryRow-'+i).hide();
						$('#accountRow-'+i).show();
					}
					
					// Check if there is some data from POST (controller injects data to the lists)
					if ($('#mainCategoryList-'+i).val() == 0) { // Add new
						
						$('#newMainCategoryRow-'+i).show();
						$('#subCategoryRow-'+i).hide();
						
					} else if ($('#mainCategoryList-'+i).val() == -1) { // Select
						
						$('#newMainCategoryRow-'+i).hide();
						$('#subCategoryRow-'+i).hide();
						
					} else { // Is main category selected
						
						$('#newMainCategoryRow-'+i).hide();
						$('#subCategoryRow-'+i).show();
						
					}
					if ($('#subCategoryList-'+i).val() == 0) { // Add new
						
						$('#newSubCategoryRow-'+i).show();
						
					} else {
						
						$('#newSubCategoryRow-'+i).hide();
						
					}
					
				}
				
			},
			initChangeSubCategory: function(trCount) {
				
				for (var i=0; i<trCount; i++) {

					// Change subcategory
					$('#subCategoryList-'+i).change(function(){
					
						// Get nr of changed select
						var nr = this.name.split('-')[1];

						// Check selection
						if ($('option:selected', this).val() == 0) { // Add new category
							
							$('#newSubCategoryRow-'+nr).show();
							
						} else {
							
							$('#newSubCategoryRow-'+nr).hide();
							
						}

					});
				}
				
			},
			initAddSubCategory: function(trCount) {
				
				for (var i=0; i<trCount; i++) {
					
					// Click add subcategory
					$('#submitNewSubCategory-'+i).click(function(){

						$('#catWait').show();
						
						// Get nr of clicked button
						var nr = this.id.split('-')[1];
						
						// Get transaction type
						var transactionType = $('#t_type-'+nr).val();
						
						// Get parent category id
						var pcid = $('#mainCategoryList-'+nr).val();
						
						// Read category name
						var catName = $('#newSubCategory-'+nr).val();
						
						// Check if there is text
						if (catName != "") {
							
							// Add category
							transaction.addCategory(catName, pcid, transactionType, nr);
							
						} else {
							
							alert('Podaj nazwę kategorii!');
							
							$('#catWait').hide();
							
						}

				    });
					
				}
				
			},
			initChangeMainCategory: function(trCount) {

				for (var i=0; i<trCount; i++) {

					// Change parent category
					$('#mainCategoryList-'+i).change(function(){
						
						// Get nr of changed select
						var nr = this.name.split('-')[1];
						
						// Check selection
						if ($('option:selected', this).val() == 0) { // Add new category
							
							$('#newMainCategoryRow-'+nr).show();
							$('#subCategoryRow-'+nr).hide();
							
						} else if ($('option:selected', this).val() == -1) { // Select category...
							
							$('#newMainCategoryRow-'+nr).hide();
							$('#subCategoryRow-'+nr).hide();
							
						} else { // Select some category
							
							$('#newMainCategoryRow-'+nr).hide();
							$('#subCategoryRow-'+nr).show();
							
							// Read main category id
							var cid = $('#mainCategoryList-'+nr).val();
							
							// Read category type
							var ctype = $('#t_type-'+nr).val();
							
							//transaction.disableControlls(true);
							
							$('#catWait').show();
							
							// load subcategories
							transaction.loadCategories(cid, ctype, nr);
						}

				    });
					
				}
				
			},
			/**
			 *  Load categories
			 *  
			 *  @param int cid Category id
			 *  @param int ctype Category type (0 - income, 1 - expense)
			 *  @param int nr Row number
			 */
			loadCategories: function (cid, ctype, nr) {
				
				if (cid == null) {
					// Clear maincategories select
					$('#mainCategoryList-'+nr).find('option').remove().end();
				} else {
					// Clear subcategories select
					$('#subCategoryList-'+nr).find('option').remove().end();
				}

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
    		    		
    		    		if (cid == null) {
    		    			// Add required entry
        		    		$('#mainCategoryList-'+nr).append('<option value="-1">Wybierz...</option>');
        		    		$('#mainCategoryList-'+nr).append('<option value="0">Dodaj nową...</option>');
    		    		} else {
    		    			// Add required entry
        		    		$('#subCategoryList-'+nr).append('<option value="-1">Brak</option>');
        		    		$('#subCategoryList-'+nr).append('<option value="0">Dodaj nową...</option>');
    		    		}
    		    		
    		    		// get all categories
    		    		$.each(data, function(key, value) {
    		    			
    		    			if (value != 'OK') {
    		    				
    		    				if (cid == null) {
		    						// Add to maincategories select
            		    			$('#mainCategoryList-'+nr).append('<option value="'+value+'">'+key+'</option>');
		    					} else {
		    						// Add to subcategories select
            		    			$('#subCategoryList-'+nr).append('<option value="'+value+'">'+key+'</option>');
		    					}
    		    				
    		    			}
    		    			
    		    		});
    		    		
    		    	} else if (data.status == "noCategories") {
    		    		
    		    		if (cid == null) {
    		    			// Add required entry
        		    		$('#mainCategoryList-'+nr).append('<option value="-1">Wybierz...</option>');
        		    		$('#mainCategoryList-'+nr).append('<option value="0">Dodaj nową...</option>');
    		    		} else {
    		    			// Add required entry
        		    		$('#subCategoryList-'+nr).append('<option value="-1">Brak</option>');
        		    		$('#subCategoryList-'+nr).append('<option value="0">Dodaj nową...</option>');
    		    		}
    		    		
    		    	} else if (data.status == "noPostData") {
    		    		
    		    		alert('Wystąpił błąd!');
    		    		
    		    	}
        			
    		    	$('#catWait').hide();
    		    	
				});
				
			},
			initAddMainCategory: function(trCount) {
				
				for (var i=0; i<trCount; i++) {
					
					// Click add main category
					$('#submitNewCategory-'+i).click(function(){

						$('#catWait').show();
						
						// Get nr of clicked button
						var nr = this.id.split('-')[1];

						// Get transaction type
						var transactionType = $('#t_type-'+nr).val();
						
						// Read category name
						var catName = $('#newMainCategory-'+nr).val();
						
						// Check if there is text
						if (catName != "") {
							
							// Add category
							transaction.addCategory(catName, null, transactionType, nr);
							
						} else {
							
							alert('Podaj nazwę kategorii!');
							
							$('#catWait').hide();
							
						}

				    });
					
				}
				
			},
			/**
			 * Add category. If pcid is given - add subcategory
			 * 
			 * @param int cid Category id
			 * @param string catName Category name
			 * @param int pcid Parent category id
			 * @param int catType Category type (0 - income, 1 - expense)
			 * @param int nr Row number
			 */
			addCategory: function(catName, pcid, catType, nr) {
				
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
            			$('#newMainCategory-'+nr).val('');
        				
            			// Main category?
    					if (pcid == null) { // Main
    						// Add to list
    						transaction.insertNewCategory(null, data.cid, data.name, catType);
    						
    						// Select new category
    						$('#mainCategoryList-'+nr).val(data.cid);
    						
    						// Trigger change event
    						$('#mainCategoryList-'+nr).change();
    						
    					} else { // Subcategory
    						transaction.insertNewCategory(data.cid, pcid, data.name, catType);
    						
    						// Select new category
    						$('#subCategoryList-'+nr).val(data.cid);
    						
    						// Trigger change event
    						$('#subCategoryList-'+nr).change();
    						
    					}
        				
        			} else if (data.status == 'exists') {
        				
        				alert('Kategoria o podanej nazwie już istnieje!');
        				
        			} else if (data.status == 'badData') {
        				
        				alert('Przekazano niepoprawne dane!');
        				
        			} else if (data.status == 'noPostData') {
    		    		
    		    		alert('Wystąpił błąd!');
    		    		
    		    	}
        			
        			$('#catWait').hide();
        			
				});
				
			},
			/**
			 * Insert category into all lists with categories
			 * 
			 * @param int cid Category id
			 * @param int pcid Parent category id
			 * @param string name Category name
			 * @param int catType Category type (0 - income, 1 - expense)
			 */
			insertNewCategory: function(cid, pcid, name, catType) {
				
				var tr = $('#transactionImportForm #trCount').val();
				
				for (var i=0; i<tr; i++) {
					
					if (cid==null) { // Add into the main categories
						// Check category type
						if ($('#t_type-'+i).val()==catType) {
							console.log('#mainCategoryList-'+i);
							$('#mainCategoryList-'+i).append('<option value="'+pcid+'">'+name+'</option>');
						}
					} else { // Insert into the subcategories
						// Check category type
						if ($('#t_type-'+i).val()==catType) {
							// Insert only into the subcategories which has correct pcid
							if ($('#mainCategoryList-'+i).val()==pcid) {
								$('#subCategoryList-'+i).append('<option value="'+cid+'">'+name+'</option>');
							}
						}
					}
					
				}
				
			}
			
	};
	
	transaction.init();
	
});