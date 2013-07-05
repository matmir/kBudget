$(function() {
	
	var category = {
			
			init: function() {
				
				this.initAddCategory();
				this.initEditCategory();
				this.initDeleteCategory();
				
				$('#catWait').hide();
				
				$('#catIncomeWait').hide();
				$('#catIncomeNoSubcategories').hide();
				$('#catIncomeChildren').hide();
				$('#catIncomeEdits').hide();
				
				$('#catExpenseWait').hide();
				$('#catExpenseNoSubcategories').hide();
				$('#catExpenseChildren').hide();
				$('#catExpenseEdits').hide();
				
				this.showSubcategoriesList();
				
				// Change income parent category
				$('#catIncomeParent').change(function(){
					
					// Clear subcategory edit control
					$('#editSubCatIncome').val('');
					
					// Hide unused word
					$('#catIncomeNoSubcategories').hide();
					
					// Get selected category name
					var catName = $("#catIncomeParent option:selected").text();
					$('#editCatIncome').val(catName);

					// Get subcategories
					category.showSubcategoriesList();
					category.loadSubCategories($('option:selected', this).val(), 0);

			    });
				
				// Change expense parent category
				$('#catExpenseParent').change(function(){
					
					// Clear subcategory edit control
					$('#editSubCatExpense').val('');
					
					// Hide unused word
					$('#catExpenseNoSubcategories').hide();
					
					// Get selected category name
					var catName = $("#catExpenseParent option:selected").text();
					$('#editCatExpense').val(catName);

					// Get subcategories
					category.showSubcategoriesList();
					category.loadSubCategories($('option:selected', this).val(), 1);

			    });
				
				// Change income subcategory
				$('#catIncomeChildren').change(function(){
					
					// Get selected category name
					var catName = $("#catIncomeChildren option:selected").text();
					$('#editSubCatIncome').val(catName);

			    });
				
				// Change expense subcategory
				$('#catExpenseChildren').change(function(){
					
					// Get selected category name
					var catName = $("#catExpenseChildren option:selected").text();
					$('#editSubCatExpense').val(catName);

			    });
				
			},
			showSubcategoriesList: function () {
				
				// Check parent selected value
				if ($('#catIncomeParent').val() == null) {
					
					$('#subcategoriesList').hide();
					
				} else {
					
					$('#subcategoriesList').show();
					
				}
				
				// Check parent selected value
				if ($('#catExpenseParent').val() == null) {
					
					$('#subcategoriesExpenseList').hide();
					
				} else {
					
					$('#subcategoriesExpenseList').show();
					
				}
				
			},
			/**
			 * Disable all controlls.
			 * 
			 * @param bool disable
			 */
			disableControlls: function(disable) {
				
				if (disable) {
					
					$('#catWait').show();
					
					// ---------- INCOME ----------
					// Add
					$('#newCatIncomeButton').attr('disabled', 'disabled');
					$('#newCatIncome').attr('disabled', 'disabled');
					$('#newSubCatIncomeButton').attr('disabled', 'disabled');
					$('#newSubCatIncome').attr('disabled', 'disabled');
					// Edit
					$('#editCatIncomeButton').attr('disabled', 'disabled');
					$('#editCatIncome').attr('disabled', 'disabled');
					$('#editSubCatIncomeButton').attr('disabled', 'disabled');
					$('#editSubCatIncome').attr('disabled', 'disabled');
					// List
					$('#catIncomeParent').attr('disabled', 'disabled');
					$('#catIncomeChildren').attr('disabled', 'disabled');
					// Delete
					$('#deleteCatIncomeButton').attr('disabled', 'disabled');
					$('#deleteSubCatIncomeButton').attr('disabled', 'disabled');
					
					// ---------- EXPENSE ----------
					// Add
					$('#newCatExpenseButton').attr('disabled', 'disabled');
					$('#newCatExpense').attr('disabled', 'disabled');
					$('#newSubCatExpenseButton').attr('disabled', 'disabled');
					$('#newSubCatExpense').attr('disabled', 'disabled');
					// Edit
					$('#editCatExpenseButton').attr('disabled', 'disabled');
					$('#editCatExpense').attr('disabled', 'disabled');
					$('#editSubCatExpenseButton').attr('disabled', 'disabled');
					$('#editSubCatExpense').attr('disabled', 'disabled');
					// List
					$('#catExpenseParent').attr('disabled', 'disabled');
					$('#catExpenseChildren').attr('disabled', 'disabled');
					// Delete
					$('#deleteCatExpenseButton').attr('disabled', 'disabled');
					$('#deleteSubCatExpenseButton').attr('disabled', 'disabled');
					
				} else {
					
					$('#catWait').hide();
					
					// ---------- INCOME ----------
					// Add
					$('#newCatIncomeButton').removeAttr('disabled');
					$('#newCatIncome').removeAttr('disabled');
					$('#newSubCatIncomeButton').removeAttr('disabled');
					$('#newSubCatIncome').removeAttr('disabled');
					// Edit
					$('#editCatIncomeButton').removeAttr('disabled');
					$('#editCatIncome').removeAttr('disabled');
					$('#editSubCatIncomeButton').removeAttr('disabled');
					$('#editSubCatIncome').removeAttr('disabled');
					// List
					$('#catIncomeParent').removeAttr('disabled');
					$('#catIncomeChildren').removeAttr('disabled');
					// Delete
					$('#deleteCatIncomeButton').removeAttr('disabled');
					$('#deleteSubCatIncomeButton').removeAttr('disabled');
					
					// ---------- EXPENSE ----------
					// Add
					$('#newCatExpenseButton').removeAttr('disabled');
					$('#newCatExpense').removeAttr('disabled');
					$('#newSubCatExpenseButton').removeAttr('disabled');
					$('#newSubCatExpense').removeAttr('disabled');
					// Edit
					$('#editCatExpenseButton').removeAttr('disabled');
					$('#editCatExpense').removeAttr('disabled');
					$('#editSubCatExpenseButton').removeAttr('disabled');
					$('#editSubCatExpense').removeAttr('disabled');
					// List
					$('#catExpenseParent').removeAttr('disabled');
					$('#catExpenseChildren').removeAttr('disabled');
					// Delete
					$('#deleteCatExpenseButton').removeAttr('disabled');
					$('#deleteSubCatExpenseButton').removeAttr('disabled');
					
				}
				
			},
			initDeleteCategory: function () {
				
				// Click delete income main category
				$('#deleteCatIncomeButton').click(function(){

					// Disable controlls
					category.disableControlls(true);
					
					// Read category id
					var cid = $('#catIncomeParent').val();
					
					if (cid == null) {
						
						alert('Wybierz kategorię z listy!');
						
						// Enable controlls
						category.disableControlls(false);
						
					} else {
						
						category.deleteCategory(cid, null, 0);
						
					}

			    });
				
				// Click delete income subcategory
				$('#deleteSubCatIncomeButton').click(function(){

					// Disable controlls
					category.disableControlls(true);
					
					// Read category id
					var cid = $('#catIncomeChildren').val();
					
					if (cid == null) {
						
						alert('Wybierz podkategorię z listy!');
						
						// Enable controlls
						category.disableControlls(false);
						
					} else {
						
						category.deleteCategory(cid, 1, 0);
						
					}

			    });
				
				// Click delete expense main category
				$('#deleteCatExpenseButton').click(function(){

					// Disable controlls
					category.disableControlls(true);
					
					// Read category id
					var cid = $('#catExpenseParent').val();
					
					if (cid == null) {
						
						alert('Wybierz kategorię z listy!');
						
						// Enable controlls
						category.disableControlls(false);
						
					} else {
						
						category.deleteCategory(cid, null, 1);
						
					}

			    });
				
				// Click delete expense subcategory
				$('#deleteSubCatExpenseButton').click(function(){

					// Disable controlls
					category.disableControlls(true);
					
					// Read category id
					var cid = $('#catExpenseChildren').val();
					
					if (cid == null) {
						
						alert('Wybierz podkategorię z listy!');
						
						// Enable controlls
						category.disableControlls(false);
						
					} else {
						
						category.deleteCategory(cid, 1, 1);
						
					}

			    });
				
			},
			/**
			 * Delete category.
			 * 
			 * @param int cid Category id
			 * @param int pcid Parent category id (only for identify where we must delete option)
			 * @param int catType Category type (0 - income, 1 - expense)
			 */
			deleteCategory: function (cid, pcid, catType) {
				
				// Add category
				$.ajax({
        			type: 'POST',
        		    url: '/user/category/delete',
        		    cache: false,
        		    data: {
        		    	'cid': cid
        		    },
        		    dataType: 'json'
        		}).done(function ( data ) {
        			  
        			// Check status
        			if (data.status == 'OK') {
        				
        				if (catType == 0) {
        					
        					if (pcid == null) {
        						
        						$('#catIncomeParent option:selected').remove();
        						
        					} else {
        						
        						$('#catIncomeChildren option:selected').remove();
        						
        					}
        					
        				} else {
        					
        					if (pcid == null) {
        						
        						$('#catExpenseParent option:selected').remove();
        						
        					} else {
        						
        						$('#catExpenseChildren option:selected').remove();
        						
        					}
        					
        				}
        				
        			} else if (data.status == 'hasSubcategories') {
        				
        				alert('Kategoria posiada subkategorie! Usuń je i spróbuj ponownie.');
        				
        			} else if (data.status == 'hasTransactions') {
        				
        				alert('Kategoria posiada transakcje! Usuń je i spróbuj ponownie.');
        				
        			} else if (data.status == 'hasTransactionsAndSubcategories') {
        				
        				alert('Kategoria posiada transakcje i podkategorie! Usuń je i spróbuj ponownie.');
        				
        			} else if (data.status == 'badData') {
        				
        				alert('Przekazano niepoprawne dane!');
        				
        			} else if (data.status == 'noPostData') {
    		    		
    		    		alert('Wystąpił błąd!');
    		    		
    		    	}
        			
        			// Enable controlls
					category.disableControlls(false);
        			
				});
				
			},
			initEditCategory: function () {
				
				// Click edit income main category
				$('#editCatIncomeButton').click(function(){

					// Disable controlls
					category.disableControlls(true);
					
					// Read category id
					var cid = $('#catIncomeParent').val();
					
					if (cid == null) {
						
						alert('Wybierz kategorię z listy!');
						
						// Enable controlls
						category.disableControlls(false);
						
					} else {
						
						// Read new category name
						var newCatName = $('#editCatIncome').val();
						
						// Check if there is text
						if (newCatName != "") {

							// Edit category
							category.saveCategory(cid, newCatName, null, 0);
							
						}
						
					}

			    });
				
				// Click edit income subcategory
				$('#editSubCatIncomeButton').click(function(){

					// Disable controlls
					category.disableControlls(true);
					
					// Read category id
					var cid = $('#catIncomeChildren').val();
					
					if (cid == null) {
						
						alert('Wybierz podkategorię z listy!');
						
						// Enable controlls
						category.disableControlls(false);
						
					} else {
						
						// Read new category name
						var newCatName = $('#editSubCatIncome').val();
						
						// Get parent category id
						var pcid = $('#catIncomeParent').val();
						
						if (pcid !==null) {
							
							// Check if there is text
							if (newCatName != "") {
								
								// Edit category
								category.saveCategory(cid, newCatName, pcid, 0);
								
							}
							
						} else {
							
							alert('Wybierz główną kategorię!');
							
							// Enable controlls
							category.disableControlls(false);
							
						}
						
						
					}

			    });
				
				// Click edit expense main category
				$('#editCatExpenseButton').click(function(){

					// Disable controlls
					category.disableControlls(true);
					
					// Read category id
					var cid = $('#catExpenseParent').val();
					
					if (cid == null) {
						
						alert('Wybierz kategorię z listy!');
						
						// Enable controlls
						category.disableControlls(false);
						
					} else {
						
						// Read new category name
						var newCatName = $('#editCatExpense').val();
						
						// Check if there is text
						if (newCatName != "") {
							
							// Edit category
							category.saveCategory(cid, newCatName, null, 1);
							
						}
						
					}

			    });
				
				// Click edit expense subcategory
				$('#editSubCatExpenseButton').click(function(){

					// Disable controlls
					category.disableControlls(true);
					
					// Read category id
					var cid = $('#catExpenseChildren').val();
					
					if (cid == null) {
						
						alert('Wybierz podkategorię z listy!');
						
						// Enable controlls
						category.disableControlls(false);
						
					} else {
						
						// Read new category name
						var newCatName = $('#editSubCatExpense').val();
						
						// Get parent category id
						var pcid = $('#catExpenseParent').val();
						
						if (pcid !==null) {
							
							// Check if there is text
							if (newCatName != "") {

								// Edit category
								category.saveCategory(cid, newCatName, pcid, 1);
								
							}
							
						} else {
							
							alert('Wybierz główną kategorię!');
							
							// Enable controlls
							category.disableControlls(false);
							
						}
						
						
					}

			    });
				
			},
			initAddCategory: function () {
				
				// Click add income main category
				$('#newCatIncomeButton').click(function(){

					// Disable controlls
					category.disableControlls(true);
					
					// Read category name
					var catName = $('#newCatIncome').val();
					
					// Check if there is text
					if (catName != "") {
						
						// Add category
						category.saveCategory(null, catName, null, 0);
						
					} else {
						
						alert('Podaj nazwę kategorii!');
						
						// Enable controlls
						category.disableControlls(false);
						
					}

			    });
				
				// Click add income subcategory
				$('#newSubCatIncomeButton').click(function(){
					
					// Disable controlls
					category.disableControlls(true);
					
					// Read category name
					var catName = $('#newSubCatIncome').val();
					
					// Get parent category id
					var pcid = $('#catIncomeParent').val();
					
					if (pcid !==null) {
						
						// Check if there is text
						if (catName != "") {
							
							// Add category
							category.saveCategory(null, catName, pcid, 0);
							
						}
						
					} else {
						
						alert('Wybierz główną kategorię!');
						
						// Enable controlls
						category.disableControlls(false);
						
					}

			    });
				
				// Click add expense main category
				$('#newCatExpenseButton').click(function(){

					// Disable controlls
					category.disableControlls(true);
					
					// Read category name
					var catName = $('#newCatExpense').val();
					
					// Check if there is text
					if (catName != "") {
						
						// Add category
						category.saveCategory(null, catName, null, 1);
						
					} else {
						
						alert('Podaj nazwę kategorii!');
						
						// Enable controlls
						category.disableControlls(false);
						
					}

			    });
				
				// Click add expense subcategory
				$('#newSubCatExpenseButton').click(function(){
					
					// Disable controlls
					category.disableControlls(true);
					
					// Read category name
					var catName = $('#newSubCatExpense').val();
					
					// Get parent category id
					var pcid = $('#catExpenseParent').val();
					
					if (pcid !==null) {
						
						// Check if there is text
						if (catName != "") {
							
							// Add category
							category.saveCategory(null, catName, pcid, 1);
							
						}
						
					} else {
						
						alert('Wybierz główną kategorię!');
						
						// Enable controlls
						category.disableControlls(false);
						
					}

			    });
				
			},
			/**
			 * Add\Edit category. If pcid is given - add subcategory
			 * 
			 * @param int cid Category id
			 * @param string catName Category name
			 * @param int pcid Parent category id
			 * @param int catType Category type (0 - income, 1 - expense)
			 */
			saveCategory: function (cid, catName, pcid, catType) {
				
				// Add category
				$.ajax({
        			type: 'POST',
        		    url: '/user/category/save',
        		    cache: false,
        		    data: {
        		    	'categoryId': cid,
        		    	'parentCategoryId': pcid,
                        'categoryType': catType,
                        'categoryName': catName,
        		    },
        		    dataType: 'json'
        		}).done(function ( data ) {
        			
        			// Clear add edits
        			$('#newCatIncome').val('');
        			$('#newSubCatIncome').val('');
        			$('#newCatExpense').val('');
        			$('#newSubCatExpense').val('');
        			
        			// Check status
        			if (data.status == 'OK') {
        				
        				// Edited?
        				if (cid == null) { // Added
        					
        					// Update category list
            				if (catType == 0) {
            					
            					// Main category?
            					if (pcid == null) { // Main
            						
            						$('#catIncomeParent').append('<option value="'+data.cid+'">'+data.name+'</option>');
            						
            					} else { // Subcategory
            						
            						$('#catIncomeNoSubcategories').hide();
            						$('#catIncomeChildren').show();
            						$('#catIncomeEdits').show();
            						
            						$('#catIncomeChildren').append('<option value="'+data.cid+'">'+data.name+'</option>');
            						
            					}
            					
            				} else {
            					
            					// Main category?
            					if (pcid == null) { // Main
            						
            						$('#catExpenseParent').append('<option value="'+data.cid+'">'+data.name+'</option>');
            						
            					} else { // Subcategory
            						
            						$('#catExpenseNoSubcategories').hide();
            						$('#catExpenseChildren').show();
            						$('#catExpenseEdits').show();
            						
            						$('#catExpenseChildren').append('<option value="'+data.cid+'">'+data.name+'</option>');
            						
            					}
            					
            				}
        					
        				} else { // Edited
        					
        					// Update category list
            				if (catType == 0) {
            					
            					// Main category?
            					if (pcid == null) { // Main
            						
            						$("#catIncomeParent option:selected").text(data.name);
            						
            					} else { // Subcategory
            						
            						$("#catIncomeChildren option:selected").text(data.name);
            						
            					}
            					
            				} else {
            					
            					// Main category?
            					if (pcid == null) { // Main
            						
            						$("#catExpenseParent option:selected").text(data.name);
            						
            					} else { // Subcategory
            						
            						$("#catExpenseChildren option:selected").text(data.name);
            						
            					}
            					
            				}
        					
        				}
        				
        			} else if (data.status == 'exists') {
        				
        				alert('Kategoria o podanej nazwie już istnieje!');
        				
        			} else if (data.status == 'badData') {
        				
        				alert('Przekazano niepoprawne dane!');
        				
        			} else if (data.status == 'noPostData') {
    		    		
    		    		alert('Wystąpił błąd!');
    		    		
    		    	}
        			
        			// Enable controlls
					category.disableControlls(false);
        			
				});
				
			},
			/**
			 *  Load sub categories
			 *  
			 *  @param int cid Category id
			 *  @param int ctype Category type (0 - income, 1 - expense)
			 */
			loadSubCategories: function (cid, ctype) {
				
				if (ctype == 0) {
					
					// Clear subcategories select
					$('#catIncomeChildren').find('option').remove().end();
					// Hide subcategories
					$('#catIncomeChildren').hide();
					$('#catIncomeEdits').hide();
					
					$('#catIncomeWait').show();
					
				} else {
					
					// Clear subcategories select
					$('#catExpenseChildren').find('option').remove().end();
					// Hide subcategories
					$('#catExpenseChildren').hide();
					$('#catExpenseEdits').hide();
					
					$('#catExpenseWait').show();
					
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
    		    		
    		    		if (ctype == 0) {
    		    			
    		    			$('#catIncomeChildren').show();
        		    		$('#catIncomeEdits').show();
    		    			
    		    		} else {
    		    			
    		    			$('#catExpenseChildren').show();
        		    		$('#catExpenseEdits').show();
    		    			
    		    		}
    		    		
    		    		// get all categories
    		    		$.each(data, function(key, value) {
    		    			
    		    			if (value != 'OK') {
    		    				
    		    				if (ctype == 0) {
    		    					
    		    					// Add to subcategories select
            		    			$('#catIncomeChildren').append('<option value="'+value+'">'+key+'</option>');
    		    					
    		    				} else {
    		    					
    		    					// Add to subcategories select
            		    			$('#catExpenseChildren').append('<option value="'+value+'">'+key+'</option>');
    		    					
    		    				}
    		    				
    		    			}
    		    			
    		    		});
    		    		
    		    	} else if (data.status == "noCategories") {
    		    		
    		    		if (ctype == 0) {
    		    			
    		    			$('#catIncomeNoSubcategories').show();
    		    			
    		    		} else {
    		    			
    		    			$('#catExpenseNoSubcategories').show();
    		    			
    		    		}
    		    		
    		    	} else if (data.status == "noPostData") {
    		    		
    		    		alert('Wystąpił błąd!');
    		    		
    		    	}
        			
    		    	if (ctype == 0) {
    		    		
    		    		$('#catIncomeWait').hide();
    		    		
    		    	} else {
    		    		
    		    		$('#catExpenseWait').hide();
    		    		
    		    	}
    		    	
				});
				
			}
			
	};
	
	category.init();
	
});
