(function(window, angular, $) {
	"use strict";
	
	//=========================================================
	// Application Model Data 
	// see the services.js and controllers.js script
	//=========================================================
	var _easyFileAppData = {
		VERSION: '1.1.0',
		inprocess: 0,
		winWidth: 0,
		winHeight: 0,
		
		mainMenu: {
			filemanager : {
				id: 'filemanager',
				isActive: true,
				templateUrl: 'templates/filemanager.html',
				refresh: function(appData) {
					appData.fileManager.fn.itemsRefresh(appData);
				}
			}
		},
		
		fileManager: {
			items: [],
			itemsTotal: 0,
			itemsPerPage: 25,
			page: 0,
			pages: [],
			selectedAll: false,
			path: [],
			clipboard: {
				operation: null,
				items: []
			},
			
			fn: {
				itemsInit: function(appData) {
					appData.fileManager.fn.itemsRefresh(appData);
				},
				itemsRefresh: function(appData) {
					var path = appData.fileManager.fn.getCurrentPath(appData),
					page = appData.fileManager.page;
					appData.fileManager.fn.getItems(appData, page, path);
				},
				selectAll: function(appData) {
					for (var index = 0, len = appData.fileManager.items.length; index < len; ++index) {
						appData.fileManager.items[index].selected = appData.fileManager.selectedAll;
					}
				},
				selectRow: function(appData, item) {
					item.selected = !item.selected;
				},
				isSelected: function(appData) {
					for (var index = 0, len = appData.fileManager.items.length; index < len; ++index) {
						if(appData.fileManager.items[index].selected)
							return true;
					}
					return false;
				},
				isSelectedAll: function(appData) {
					for (var index = 0, len = appData.fileManager.items.length; index < len; ++index) {
						if(!appData.fileManager.items[index].selected) {
							return false;
						}
					}
					if(appData.fileManager.items.length > 0) {
						return true;
					}
					return false;
				},
				getSelectedItems: function(appData) {
					var items = [],
					path = appData.fileManager.fn.getCurrentPath(appData);
					for (var index = 0, len = appData.fileManager.items.length; index < len; ++index) {
						if(appData.fileManager.items[index].selected) {
							var item = {
								id: appData.fileManager.items[index].id,
								name: appData.fileManager.items[index].name,
								path: path
							}
							items.push(item);
						}
					}
					return items;
				},
				getItems: function(appData, page, path) {
					var postData = {
						action: 'list',
						page: page,
						itemsPerPage: appData.fileManager.itemsPerPage,
						path: path
					};
					appData.inprocess++;
					
					appData.service.$http.post('filemanager', postData).success(function(data, code) {
						var items = [];
						if(data.items) {
							for (var i = 0, len = data.items.length; i < len; ++i) {
								var item = angular.merge({}, data.items[i]);
								item.selected = false;
								//item.id
								//item.name
								//item.link
								//item.size
								item.created_at = appData.fn.parseDate(item.created_at);
								item.updated_at = appData.fn.parseDate(item.updated_at);
								items.push(item);
							}
						}
						
						var pageCount = Math.ceil(data.total / data.itemsPerPage),
						pages = new Array (pageCount);
						for (var i = 0; i < pageCount; i++) {
							pages[i] = i + 1;
						}
						
						appData.fileManager.items = items;
						appData.fileManager.itemsTotal = data.total;
						appData.fileManager.page = data.page;
						appData.fileManager.path = data.path;
						appData.fileManager.pages = pages;
					}).error(function(data, code) {
						appData.service.growl.error("The list operation failed");
						console.log('Error %s - response error, please check the ajax response.'.replace('%s', code));
					})['finally'](function() {
						appData.inprocess--;
					});
				},
				getFolderItems: function(appData, item) {
					var path = appData.fileManager.fn.getCurrentPath(appData);
					path = path + (path == '' ? '' : '/') + item.name;
					appData.fileManager.fn.getItems(appData, 0, path);
				},
				getFolderItemsBreadcrumb: function(appData, item) {
					appData.fileManager.fn.getItems(appData, 0, item.path);
				},
				getPrevPage: function(appData) {
					var page = appData.fileManager.page - 1;
					if(page < 0)
						return;
					
					var path = appData.fileManager.fn.getCurrentPath(appData);
					appData.fileManager.fn.getItems(appData, page, path);
				},
				getNextPage: function(appData) {
					var page = appData.fileManager.page + 1;
					if(page >= appData.fileManager.pages.length)
						return;
					
					var path = appData.fileManager.fn.getCurrentPath(appData);
					appData.fileManager.fn.getItems(appData, page, path);
				},
				getPage: function(appData, page) {
					var path = appData.fileManager.fn.getCurrentPath(appData);
					appData.fileManager.fn.getItems(appData, page, path);
				},
				getCurrentPath: function(appData) {
					var path = '';
					for (var i = 1, len = appData.fileManager.path.length; i < len; ++i) {
						path += appData.fileManager.path[i].name + (i+1 == len ? '' : '/');
					}
					return path;
				},
				removeItemsConfirm: function(appData) {
					if(!appData.fileManager.fn.isSelected(appData)) {
						appData.service.growl.warning("You should select items to delete");
						return;
					}
					
					var modalData = {
						content: 'Are you sure to delete selected items?'
					}
					appData.modal.fn.show(appData, 'templates/modal-confirm.html', modalData).then(function(result) {
						appData.modal.fn.close(appData, modalData.id);
						
						if(result == 'close') {
							return;
						}
						
						if(result) {
							appData.fileManager.fn.removeItems(appData);
						}
					});
				},
				removeItems: function(appData) {
					var path = appData.fileManager.fn.getCurrentPath(appData),
					items = appData.fileManager.fn.getSelectedItems(appData),
					itemsCount = items.length,
					postData = {
						action: 'delete',
						items: items,
						path: path
					};
					appData.inprocess++;
					
					appData.service.$http.post('filemanager', postData).success(function(data, code) {
						if(data.processed && data.processed.length == itemsCount) {
							appData.service.growl.success("The delete operation successful");
						} else {
							appData.service.growl.warning("Not all selected items are deleted");
							console.log(data.error);
						}
						appData.fileManager.fn.itemsRefresh(appData);
					}).error(function(data, code) {
						appData.service.growl.error("The delete operation failed");
						console.log('Error %s - response error, please check the ajax response.'.replace('%s', code));
					})['finally'](function() {
						appData.inprocess--;
					});
				},
				createFolderConfirm: function(appData) {
					var modalData = {
						folderName: null
					};
					appData.modal.fn.show(appData, 'templates/modal-create-folder.html', modalData).then(function(result) {
						appData.modal.fn.close(appData, modalData.id);
						
						if(result == 'close') {
							return;
						}
						
						if(result) {
							appData.fileManager.fn.createFolder(appData, modalData.folderName);
						}
					});
				},
				createFolder: function(appData, name) {
					var path = appData.fileManager.fn.getCurrentPath(appData),
					postData = {
						action: 'mkdir',
						name: name,
						path: path
					};
					appData.inprocess++;
					
					appData.service.$http.post('filemanager', postData).success(function(data, code) {
						if(data.result && data.item) {
							appData.service.growl.success("The create folder operation successful");
							
							var item = angular.merge({}, data.item);
							item.selected = false;
							//item.id
							//item.name
							//item.link
							item.size = item.size;
							item.created_at = appData.fn.parseDate(item.created_at);
							item.updated_at = appData.fn.parseDate(item.updated_at);
							appData.fileManager.items.push(item);
							
							appData.fileManager.itemsTotal++;
						} else {
							appData.service.growl.error("The create folder operation failed");
							console.log(data.error);
						}
					}).error(function(data, code) {
						appData.service.growl.error("The create folder operation failed");
						console.log('Error %s - response error, please check the ajax response.'.replace('%s', code));
					})['finally'](function() {
						appData.inprocess--;
					});
				},
				uploadFileConfirm: function(appData) {
					var path = '/' + appData.fileManager.fn.getCurrentPath(appData);
					var modalData = {
						inprocess: 0,
						progress: 0,
						path: path,
						files: [],
						fn: {
							select: function(modalData, files) {
								modalData.files = files;
							},
							close: function(modalData) {
								var i = modalData.files.length;
								while (i--) {
									modalData.fn.remove(modalData, i);
								}
								modalData.deferred.resolve('close');
							},
							remove: function(modalData, index) {
								var file = modalData.files[index];
								if(file && file.xhr && file.xhr.readyState != 4) {
									file.xhr.abort();
								}
								modalData.files.splice(index, 1);
							},
							upload: function(modalData) {
								var path = modalData.appData.fileManager.fn.getCurrentPath(modalData.appData);
								for(var index = 0; index < modalData.files.length; ++index) {
									var f = modalData.files[index];
									f.error = false;
									f.progress = 0;
									
									(function(file) {
										var postData = {
											action: 'upload',
											file: file,
											path: path
										};
										modalData.inprocess++;
										
										modalData.appData.service.upload.upload({
											url: 'filemanager', 
											data: postData
										}).progress(function(e) {
											file.progress = Math.min(100, parseInt(100.0 * e.loaded / e.total)) - 1;
										}).success(function(data, code) {
											if(typeof(data.result) !== "boolean" || !data.result) {
												file.error = true;
											}
										}).error(function(data, code) {
											file.error = true;
											console.log('Error %s - response error, please check the ajax response.'.replace('%s', code));
										}).xhr(function(xhr) {
											file.xhr = xhr;
										})['finally'](function() {
											modalData.inprocess--;
											
											setTimeout(function() {
												if(file.error) {
													file.progress = 0;
													console.log('Response error, please check the ajax response.');
												} else {
													file.progress = 100;
												}
												
												modalData.appData.service.$root.safeApply();
											}, 1 + 100 * modalData.inprocess);
										});
									})(f);
								}
							}
						}
					};
					appData.modal.fn.show(appData, 'templates/modal-upload-file.html', modalData).then(function(result) {
						appData.modal.fn.close(appData, modalData.id);
						
						if(result == 'close') {
							appData.fileManager.fn.itemsRefresh(modalData.appData);
							return;
						}
					});
				},
				editItemOpen: function(appData, item) {
					item.tmpname = item.name;
					item.edit = true;
				},
				renameItem: function(appData, item) {
					var path = appData.fileManager.fn.getCurrentPath(appData),
					postData = {
						action: 'rename',
						oldname: item.name,
						newname: item.tmpname,
						path: path
					};
					appData.inprocess++;
					
					appData.service.$http.post('filemanager', postData).success(function(data, code) {
						if(data.result) {
							item.tmpname = null;
							item.edit = false;
							item.name = data.name;
							appData.service.growl.success("The rename operation successful");
						} else {
							appData.service.growl.error("The rename operation failed");
							console.log(data.error);
						}
					}).error(function(data, code) {
						appData.service.growl.error("The rename operation failed");
						console.log('Error %s - response error, please check the ajax response.'.replace('%s', code));
					})['finally'](function() {
						appData.inprocess--;
					});
				},
				editItemClose: function(appData, item) {
					item.tmpname = null;
					item.edit = false;
				},
				cutItems: function(appData) {
					var items = appData.fileManager.fn.getSelectedItems(appData);
					if(items.length > 0) {
						appData.fileManager.clipboard.operation = 'cut';
						appData.fileManager.clipboard.items = items;
					}
				},
				copyItems: function(appData) {
					var items = appData.fileManager.fn.getSelectedItems(appData);
					if(items.length > 0) {
						appData.fileManager.clipboard.operation = 'copy';
						appData.fileManager.clipboard.items = items;
					}
				},
				pasteItemsConfirm: function(appData) {
					var modalData = {
						content: 'Are you sure to ' + appData.fileManager.clipboard.operation + ' items?',
					}
					appData.modal.fn.show(appData, 'templates/modal-confirm.html', modalData).then(function(result) {
						appData.modal.fn.close(appData, modalData.id);
						
						if(result == 'close') {
							return;
						}
						
						if(result) {
							appData.fileManager.fn.pasteItems(appData);
						}
					});
				},
				pasteItems: function(appData) {
					var path = appData.fileManager.fn.getCurrentPath(appData),
					items = appData.fileManager.clipboard.items,
					itemsCount = items.length,
					operation = appData.fileManager.clipboard.operation,
					postData = {
						action: 'paste',
						items: items,
						operation: operation,
						path: path
					};
					appData.inprocess++;
					
					appData.service.$http.post('filemanager', postData).success(function(data, code) {
						if(data.processed && data.processed.length == itemsCount) {
							appData.service.growl.success("The paste operation successful");
						} else {
							appData.service.growl.warning("Not all items are processed");
							console.log(data.error);
						}
						
						if(postData.operation == 'cut') {
							appData.fileManager.clipboard.operation = null;
							appData.fileManager.clipboard.items = [];
						}
						
						appData.fileManager.fn.itemsRefresh(appData);
					}).error(function(data, code) {
						appData.service.growl.error("The paste operation failed");
						console.log('Error %s - response error, please check the ajax response.'.replace('%s', code));
					})['finally'](function() {
						appData.inprocess--;
					});
				}
			}
		},
		
		modal: {
			count: 0,
			items: [],
			
			fn: {
				show: function(appData, templateUrl, modalData, callback) {
					var id = ++appData.modal.count,
					deferred = appData.service.$q.defer();
					
					appData.modal.items.push(id);
					appData.inprocess++;
					
					appData.service.$templateRequest(templateUrl).then(function(html) {
						var template = angular.element(html); // convert the html to an actual DOM node
						
						// append it to the directive element
						jQuery('body').addClass('efile-ui-modal-open');
						
						if(modalData.easyClose == undefined || modalData.easyClose) {
							template.on('click', function(e) {
								scope.modalData.deferred.resolve('close');
							});
						}
						
						template.find(".efile-ui-modal-dialog").on('click', function(e) {
							e.preventDefault();
							return false;
						});
						
						
						jQuery('.efile-ui-modals').append(template);
						
					
						// create a new isolated scope
						var scope = appData.service.$root.$new(true);
						scope.modalData = modalData;
						scope.modalData.id = id;
						scope.modalData.deferred = deferred;
						scope.modalData.appData = appData;
						
						// and let Angular $compile it
						appData.service.$compile(template)(scope);
						
						
					})['finally'](function() {
						if (callback && typeof callback == 'function') { // make sure the callback is a function
							callback.call(this); // brings the scope to the callback
						}
						appData.inprocess--;
					});
					
					return deferred.promise;
				},
				close: function(appData, id) {
					jQuery('#efile-ui-modal-' + id).remove();
					
					var index = appData.modal.items.indexOf(id);
					appData.modal.items.splice(index, 1);
					if(appData.modal.items.length == 0) {
						jQuery('body').removeClass('efile-ui-modal-open');
					}
				}
			}
		},
		
		fn: {
			init: function(appData) {
				jQuery(window).on('resize', jQuery.proxy(function() {
					this.fn.resize(this);
				}, appData));
				
				appData.fn.resize(appData);
			},
			resize: function(appData) {
				var $win = jQuery(window);
				appData.winWidth = $win.outerWidth();
				appData.winHeight = $win.outerHeight();
				
				appData.service.$root.safeApply();
			},
			mainMenuItemInit: function(appData, id) {
				jQuery('#efile-ui-menu-item-' + id).on('click', function(e) {
					e.preventDefault();
					appData.fn.mainMenuItemClick(appData, id);
				});
				
				if(appData.mainMenu[id].isActive) {
					appData.fn.workspaceInit(appData, id);
					appData.service.$root.safeApply();
				}
			},
			mainMenuItemClick: function(appData, id) {
				if(!appData.mainMenu[id].isActive) {
					appData.fn.workspaceInit(appData, id);
					appData.fn.mainMenuReset(appData);
					
					appData.mainMenu[id].isActive = true;
					appData.service.$root.safeApply();
				}
			},
			mainMenuReset: function(appData) {
				var obj = appData.mainMenu;
				for (var property in obj) {
					if (obj.hasOwnProperty(property)) {
						obj[property].isActive = false;
					}
				}
			},
			refreshWorkspace: function(appData) {
				var obj = appData.mainMenu;
				for (var property in obj) {
					if (obj.hasOwnProperty(property)) {
						if(obj[property].isActive) {
							obj[property].refresh(appData);
						};
					}
				}
			},
			workspaceInit: function(appData, id) {
				var url = appData.mainMenu[id].templateUrl;
				appData.inprocess++;
				appData.service.$templateRequest(url).then(function(data) {
					// convert the html to an actual DOM node
					var template = angular.element(data);
					// append it to the directive element
					jQuery('#efile-ui-workspace').empty().append(template);
					
					// create a new isolated scope
					var scope = appData.service.$root.$new(true);
					scope.appData = appData;
					
					// and let Angular $compile it
					appData.service.$compile(template)(scope);
				})['finally'](function() {
					appData.inprocess--;
				});
			},
			
			// helpers
			parseDate: function(date) {
				var d = (date || '').toString().split(/[- :]/);
				return new Date(d[0], d[1] - 1, d[2], d[3], d[4], d[5]);
			}
		},
		
		// AngularJS services (see controllers.js)
		service : {
			$compile: null,
			$timeout: null,
			$templateRequest: null,
			$http: null,
			$scope: null
		}
	};
	window.easyFileAppData = _easyFileAppData;
	
	
	//=========================================================
	// Angular Modules
	//=========================================================
	angular.module('ngEasyFileApp', [
		'ngEasyFileApp.services',
		'ngEasyFileApp.controllers',
		'ngEasyFileApp.directives',
		'ngEasyFileApp.filters',
		'angular-growl',
		'ngAnimate',
		'ngFileUpload'
	]).
	config(['growlProvider', function(growlProvider) {
		growlProvider.globalTimeToLive(9000);
		growlProvider.globalDisableCloseButton(false);
		growlProvider.onlyUniqueMessages(false);
	}]).
	run(['$rootScope', function($rootScope) {
		$rootScope.safeApply = function safeApply(operation) {
			var phase = this.$root.$$phase;
			if (phase !== '$apply' && phase !== '$digest') {
				this.$apply(operation);
				return;
			}

			if (operation && typeof operation === 'function')
				operation();
		};
	}]);
	//=========================================================
	// Services
	//=========================================================
	angular.module('ngEasyFileApp.services', [])
	.factory('appData', function () {
		return _easyFileAppData;
	});
	//=========================================================
	// Controllers
	//=========================================================
	angular.module('ngEasyFileApp.controllers', []).
		controller('ngEasyFileAppController', ['$scope', '$compile', '$timeout', '$templateRequest', '$http', '$q', 'growl', 'Upload', 'appData', function ($scope, $compile, $timeout, $templateRequest, $http, $q, growl, Upload, appData) {
			appData.service.$compile = $compile;
			appData.service.$timeout = $timeout;
			appData.service.$templateRequest = $templateRequest;
			appData.service.$http = $http;
			appData.service.$q = $q;
			appData.service.growl = growl;
			appData.service.upload = Upload;
			appData.service.$root = $scope.$root;
			$scope.appData = appData;
	}]);
	//=========================================================
	// Directives
	//=========================================================
	angular.module('ngEasyFileApp.directives', []).
	directive('pressEnter', function () {
		return function (scope, element, attrs) {
			element.bind("keydown keypress", function (e) {
				if(e.which === 13) {
					scope.$apply(function () {
						scope.$eval(attrs.pressEnter);
					});
					e.preventDefault();
				}
			});
		};
	}).
	directive('pressEsc', function () {
		return function (scope, element, attrs) {
			element.bind("keydown keypress", function (e) {
				if(e.which === 27) {
					scope.$apply(function () {
						scope.$eval(attrs.pressEsc);
					});
					e.preventDefault();
				}
			});
		};
	});
	//=========================================================
	// Filters
	//=========================================================
	angular.module('ngEasyFileApp.filters', []).
	filter('fileSize', function() {
		var byteUnits = ['Byte', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		
		return function(size) {
			if(size == 0)
				return size + ' Byte';
			var i = Math.floor( Math.log(size) / Math.log(1024) );
			return ( size / Math.pow(1024, i) ).toFixed(0) * 1 + ' ' + byteUnits[i];
		}
    });
	//=========================================================
})(window, angular, jQuery);