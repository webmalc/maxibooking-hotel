<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appDevUrlMatcher.
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appDevUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);
        $context = $this->context;
        $request = $this->request;

        if (0 === strpos($pathinfo, '/_')) {
            // _wdt
            if (0 === strpos($pathinfo, '/_wdt') && preg_match('#^/_wdt/(?P<token>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_wdt')), array (  '_controller' => 'web_profiler.controller.profiler:toolbarAction',));
            }

            if (0 === strpos($pathinfo, '/_profiler')) {
                // _profiler_home
                if (rtrim($pathinfo, '/') === '/_profiler') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', '_profiler_home');
                    }

                    return array (  '_controller' => 'web_profiler.controller.profiler:homeAction',  '_route' => '_profiler_home',);
                }

                if (0 === strpos($pathinfo, '/_profiler/search')) {
                    // _profiler_search
                    if ($pathinfo === '/_profiler/search') {
                        return array (  '_controller' => 'web_profiler.controller.profiler:searchAction',  '_route' => '_profiler_search',);
                    }

                    // _profiler_search_bar
                    if ($pathinfo === '/_profiler/search_bar') {
                        return array (  '_controller' => 'web_profiler.controller.profiler:searchBarAction',  '_route' => '_profiler_search_bar',);
                    }

                }

                // _profiler_purge
                if ($pathinfo === '/_profiler/purge') {
                    return array (  '_controller' => 'web_profiler.controller.profiler:purgeAction',  '_route' => '_profiler_purge',);
                }

                // _profiler_info
                if (0 === strpos($pathinfo, '/_profiler/info') && preg_match('#^/_profiler/info/(?P<about>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_info')), array (  '_controller' => 'web_profiler.controller.profiler:infoAction',));
                }

                // _profiler_phpinfo
                if ($pathinfo === '/_profiler/phpinfo') {
                    return array (  '_controller' => 'web_profiler.controller.profiler:phpinfoAction',  '_route' => '_profiler_phpinfo',);
                }

                // _profiler_search_results
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/search/results$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_search_results')), array (  '_controller' => 'web_profiler.controller.profiler:searchResultsAction',));
                }

                // _profiler
                if (preg_match('#^/_profiler/(?P<token>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler')), array (  '_controller' => 'web_profiler.controller.profiler:panelAction',));
                }

                // _profiler_router
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/router$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_router')), array (  '_controller' => 'web_profiler.controller.router:panelAction',));
                }

                // _profiler_exception
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/exception$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_exception')), array (  '_controller' => 'web_profiler.controller.exception:showAction',));
                }

                // _profiler_exception_css
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/exception\\.css$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_exception_css')), array (  '_controller' => 'web_profiler.controller.exception:cssAction',));
                }

            }

            if (0 === strpos($pathinfo, '/_configurator')) {
                // _configurator_home
                if (rtrim($pathinfo, '/') === '/_configurator') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', '_configurator_home');
                    }

                    return array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::checkAction',  '_route' => '_configurator_home',);
                }

                // _configurator_step
                if (0 === strpos($pathinfo, '/_configurator/step') && preg_match('#^/_configurator/step/(?P<index>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_configurator_step')), array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::stepAction',));
                }

                // _configurator_final
                if ($pathinfo === '/_configurator/final') {
                    return array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::finalAction',  '_route' => '_configurator_final',);
                }

            }

        }

        if (0 === strpos($pathinfo, '/warehouse')) {
            if (0 === strpos($pathinfo, '/warehouse/invoice')) {
                // warehouse_invoice
                if (rtrim($pathinfo, '/') === '/warehouse/invoice') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_warehouse_invoice;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'warehouse_invoice');
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\InvoiceController::indexAction',  '_route' => 'warehouse_invoice',);
                }
                not_warehouse_invoice:

                // warehouse_invoice_new
                if ($pathinfo === '/warehouse/invoice/new') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_warehouse_invoice_new;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\InvoiceController::newAction',  '_route' => 'warehouse_invoice_new',);
                }
                not_warehouse_invoice_new:

                // warehouse_invoice_create
                if ($pathinfo === '/warehouse/invoice/create') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_warehouse_invoice_create;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\InvoiceController::createAction',  '_route' => 'warehouse_invoice_create',);
                }
                not_warehouse_invoice_create:

                // warehouse_invoice_edit
                if (preg_match('#^/warehouse/invoice/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_warehouse_invoice_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_invoice_edit')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\InvoiceController::editAction',));
                }
                not_warehouse_invoice_edit:

                // warehouse_invoice_update
                if (preg_match('#^/warehouse/invoice/(?P<id>[^/]++)/update$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_warehouse_invoice_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_invoice_update')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\InvoiceController::updateAction',));
                }
                not_warehouse_invoice_update:

                // warehouse_invoice_delete
                if (preg_match('#^/warehouse/invoice/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_warehouse_invoice_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_invoice_delete')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\InvoiceController::deleteAction',));
                }
                not_warehouse_invoice_delete:

            }

            if (0 === strpos($pathinfo, '/warehouse/record')) {
                // warehouse_record
                if (rtrim($pathinfo, '/') === '/warehouse/record') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_warehouse_record;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'warehouse_record');
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\RecordController::indexAction',  '_route' => 'warehouse_record',);
                }
                not_warehouse_record:

                // records_json
                if ($pathinfo === '/warehouse/record/json') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_records_json;
                    }

                    return array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\RecordController::jsonAction',  '_route' => 'records_json',);
                }
                not_records_json:

                if (0 === strpos($pathinfo, '/warehouse/record/inventory')) {
                    // warehouse_record_inventory
                    if ($pathinfo === '/warehouse/record/inventory') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_warehouse_record_inventory;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\RecordController::inventoryAction',  '_route' => 'warehouse_record_inventory',);
                    }
                    not_warehouse_record_inventory:

                    // inventory_json
                    if ($pathinfo === '/warehouse/record/inventory/json') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_inventory_json;
                        }

                        return array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\RecordController::jsonInventoryAction',  '_route' => 'inventory_json',);
                    }
                    not_inventory_json:

                }

                // warehouse_record_new
                if ($pathinfo === '/warehouse/record/new') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_warehouse_record_new;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\RecordController::newAction',  '_route' => 'warehouse_record_new',);
                }
                not_warehouse_record_new:

                // warehouse_record_create
                if ($pathinfo === '/warehouse/record/create') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_warehouse_record_create;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\RecordController::createAction',  '_route' => 'warehouse_record_create',);
                }
                not_warehouse_record_create:

                // warehouse_record_edit
                if (preg_match('#^/warehouse/record/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_warehouse_record_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_record_edit')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\RecordController::editAction',));
                }
                not_warehouse_record_edit:

                // warehouse_record_update
                if (preg_match('#^/warehouse/record/(?P<id>[^/]++)/update$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_warehouse_record_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_record_update')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\RecordController::updateAction',));
                }
                not_warehouse_record_update:

                // warehouse_record_delete
                if (preg_match('#^/warehouse/record/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_warehouse_record_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_record_delete')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\RecordController::deleteAction',));
                }
                not_warehouse_record_delete:

            }

        }

        if (0 === strpos($pathinfo, '/management/warehouse')) {
            // warehouse_category
            if (rtrim($pathinfo, '/') === '/management/warehouse') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_warehouse_category;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'warehouse_category');
                }

                return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::indexAction',  '_route' => 'warehouse_category',);
            }
            not_warehouse_category:

            // warehouse_ware_category_save
            if ($pathinfo === '/management/warehouse/') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_warehouse_ware_category_save;
                }

                return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::saveChangesAction',  '_route' => 'warehouse_ware_category_save',);
            }
            not_warehouse_ware_category_save:

            // warehouse_category_entry_new
            if (preg_match('#^/management/warehouse/(?P<id>[^/]++)/new/entry$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_warehouse_category_entry_new;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_category_entry_new')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::newEntryAction',));
            }
            not_warehouse_category_entry_new:

            // warehouse_category_entry_create
            if (preg_match('#^/management/warehouse/(?P<id>[^/]++)/create/entry$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_warehouse_category_entry_create;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_category_entry_create')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::createEntryAction',));
            }
            not_warehouse_category_entry_create:

            // warehouse_category_entry_edit
            if (preg_match('#^/management/warehouse/(?P<id>[^/]++)/edit/entry$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_warehouse_category_entry_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_category_entry_edit')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::editEntryAction',));
            }
            not_warehouse_category_entry_edit:

            // warehouse_category_entry_update
            if (preg_match('#^/management/warehouse/(?P<id>[^/]++)/update/entry$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_warehouse_category_entry_update;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_category_entry_update')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::updateEntryAction',));
            }
            not_warehouse_category_entry_update:

            // warehouse_category_new
            if ($pathinfo === '/management/warehouse/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_warehouse_category_new;
                }

                return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::newAction',  '_route' => 'warehouse_category_new',);
            }
            not_warehouse_category_new:

            // warehouse_category_create
            if ($pathinfo === '/management/warehouse/create') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_warehouse_category_create;
                }

                return array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::createAction',  '_route' => 'warehouse_category_create',);
            }
            not_warehouse_category_create:

            // warehouse_category_edit
            if (preg_match('#^/management/warehouse/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_warehouse_category_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_category_edit')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::editAction',));
            }
            not_warehouse_category_edit:

            // warehouse_category_update
            if (preg_match('#^/management/warehouse/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_warehouse_category_update;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_category_update')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::updateAction',));
            }
            not_warehouse_category_update:

            // warehouse_category_delete
            if (preg_match('#^/management/warehouse/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_warehouse_category_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_category_delete')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::deleteAction',));
            }
            not_warehouse_category_delete:

            // warehouse_category_entry_delete
            if (preg_match('#^/management/warehouse/(?P<id>[^/]++)/entry/delete$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_warehouse_category_entry_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'warehouse_category_entry_delete')), array (  '_controller' => 'MBH\\Bundle\\WarehouseBundle\\Controller\\WarehouseController::deleteEntryAction',));
            }
            not_warehouse_category_entry_delete:

        }

        if (0 === strpos($pathinfo, '/_trans')) {
            // jms_translation_update_message
            if (0 === strpos($pathinfo, '/_trans/api/configs') && preg_match('#^/_trans/api/configs/(?P<config>[^/]++)/domains/(?P<domain>[^/]++)/locales/(?P<locale>[^/]++)/messages$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_jms_translation_update_message;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'jms_translation_update_message')), array (  'id' => NULL,  '_controller' => 'JMS\\TranslationBundle\\Controller\\ApiController::updateMessageAction',));
            }
            not_jms_translation_update_message:

            // jms_translation_index
            if (rtrim($pathinfo, '/') === '/_trans') {
                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'jms_translation_index');
                }

                return array (  '_controller' => 'JMS\\TranslationBundle\\Controller\\TranslateController::indexAction',  '_route' => 'jms_translation_index',);
            }

        }

        if (0 === strpos($pathinfo, '/management')) {
            if (0 === strpos($pathinfo, '/management/client')) {
                if (0 === strpos($pathinfo, '/management/client/config')) {
                    // client_config
                    if (rtrim($pathinfo, '/') === '/management/client/config') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_client_config;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'client_config');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ClientBundle\\Controller\\ClientConfigController::indexAction',  '_route' => 'client_config',);
                    }
                    not_client_config:

                    // client_config_save
                    if ($pathinfo === '/management/client/config/') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_client_config_save;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ClientBundle\\Controller\\ClientConfigController::saveAction',  '_route' => 'client_config_save',);
                    }
                    not_client_config_save:

                    if (0 === strpos($pathinfo, '/management/client/config/payment_system')) {
                        // client_payment_system
                        if ($pathinfo === '/management/client/config/payment_system') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_client_payment_system;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\ClientBundle\\Controller\\ClientConfigController::paymentSystemAction',  '_route' => 'client_payment_system',);
                        }
                        not_client_payment_system:

                        // client_payment_system_save
                        if ($pathinfo === '/management/client/config/payment_system/save') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_client_payment_system_save;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\ClientBundle\\Controller\\ClientConfigController::paymentSystemSaveAction',  '_route' => 'client_payment_system_save',);
                        }
                        not_client_payment_system_save:

                    }

                }

                if (0 === strpos($pathinfo, '/management/client/templates')) {
                    // document_templates
                    if (rtrim($pathinfo, '/') === '/management/client/templates') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_document_templates;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'document_templates');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ClientBundle\\Controller\\DocumentTemplateController::indexAction',  '_route' => 'document_templates',);
                    }
                    not_document_templates:

                    // document_templates_new
                    if ($pathinfo === '/management/client/templates/new') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_document_templates_new;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ClientBundle\\Controller\\DocumentTemplateController::newAction',  '_route' => 'document_templates_new',);
                    }
                    not_document_templates_new:

                    // document_templates_edit
                    if (0 === strpos($pathinfo, '/management/client/templates/edit') && preg_match('#^/management/client/templates/edit/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_document_templates_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'document_templates_edit')), array (  '_controller' => 'MBH\\Bundle\\ClientBundle\\Controller\\DocumentTemplateController::editAction',));
                    }
                    not_document_templates_edit:

                    // document_templates_preview
                    if (0 === strpos($pathinfo, '/management/client/templates/preview') && preg_match('#^/management/client/templates/preview/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_document_templates_preview;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'document_templates_preview')), array (  '_controller' => 'MBH\\Bundle\\ClientBundle\\Controller\\DocumentTemplateController::previewAction',));
                    }
                    not_document_templates_preview:

                    // document_templates_show
                    if (0 === strpos($pathinfo, '/management/client/templates/show') && preg_match('#^/management/client/templates/show/(?P<id>[^/]++)/order/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_document_templates_show;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'document_templates_show')), array (  '_controller' => 'MBH\\Bundle\\ClientBundle\\Controller\\DocumentTemplateController::showAction',));
                    }
                    not_document_templates_show:

                    // document_templates_delete
                    if (0 === strpos($pathinfo, '/management/client/templates/delete') && preg_match('#^/management/client/templates/delete/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_document_templates_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'document_templates_delete')), array (  '_controller' => 'MBH\\Bundle\\ClientBundle\\Controller\\DocumentTemplateController::deleteAction',));
                    }
                    not_document_templates_delete:

                }

            }

            if (0 === strpos($pathinfo, '/management/online')) {
                if (0 === strpos($pathinfo, '/management/online/api')) {
                    // online_orders
                    if (0 === strpos($pathinfo, '/management/online/api/orders') && preg_match('#^/management/online/api/orders/(?P<begin>[^/]++)/(?P<end>[^/]++)/(?P<id>[^/]++)/(?P<sign>[^/]++)(?:/(?P<type>[^/]++))?$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_online_orders;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'online_orders')), array (  '_format' => 'xml',  'id' => NULL,  'type' => 'begin',  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\ApiController::ordersAction',));
                    }
                    not_online_orders:

                    // online_form_get
                    if (0 === strpos($pathinfo, '/management/online/api/form') && preg_match('#^/management/online/api/form(?:/(?P<id>[^/]++))?$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_online_form_get;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'online_form_get')), array (  '_format' => 'js',  'id' => NULL,  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\ApiController::getFormAction',));
                    }
                    not_online_form_get:

                    // api_success_url
                    if ($pathinfo === '/management/online/api/success/url') {
                        if (!in_array($this->context->getMethod(), array('POST', 'GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('POST', 'GET', 'HEAD'));
                            goto not_api_success_url;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\ApiController::successUrlAction',  '_route' => 'api_success_url',);
                    }
                    not_api_success_url:

                    // api_fail_url
                    if ($pathinfo === '/management/online/api/fail/url') {
                        if (!in_array($this->context->getMethod(), array('POST', 'GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('POST', 'GET', 'HEAD'));
                            goto not_api_fail_url;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\ApiController::failUrlAction',  '_route' => 'api_fail_url',);
                    }
                    not_api_fail_url:

                    // online_form_check_order
                    if ($pathinfo === '/management/online/api/order/check') {
                        if (!in_array($this->context->getMethod(), array('POST', 'GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('POST', 'GET', 'HEAD'));
                            goto not_online_form_check_order;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\ApiController::checkOrderAction',  '_route' => 'online_form_check_order',);
                    }
                    not_online_form_check_order:

                    if (0 === strpos($pathinfo, '/management/online/api/results')) {
                        // online_form_results_table
                        if (0 === strpos($pathinfo, '/management/online/api/results/table') && preg_match('#^/management/online/api/results/table(?:/(?P<id>[^/]++))?$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_online_form_results_table;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'online_form_results_table')), array (  'id' => NULL,  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\ApiController::getResultsTableAction',));
                        }
                        not_online_form_results_table:

                        // online_form_user_form
                        if ($pathinfo === '/management/online/api/results/user/form') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_online_form_user_form;
                            }

                            return array (  'id' => NULL,  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\ApiController::getUserFormAction',  '_route' => 'online_form_user_form',);
                        }
                        not_online_form_user_form:

                        if (0 === strpos($pathinfo, '/management/online/api/results/pa')) {
                            // online_form_payment_type
                            if (0 === strpos($pathinfo, '/management/online/api/results/payment/type') && preg_match('#^/management/online/api/results/payment/type(?:/(?P<id>[^/]++))?$#s', $pathinfo, $matches)) {
                                if ($this->context->getMethod() != 'POST') {
                                    $allow[] = 'POST';
                                    goto not_online_form_payment_type;
                                }

                                return $this->mergeDefaults(array_replace($matches, array('_route' => 'online_form_payment_type')), array (  'id' => NULL,  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\ApiController::getPaymentTypeAction',));
                            }
                            not_online_form_payment_type:

                            // online_form_packages_create
                            if ($pathinfo === '/management/online/api/results/packages/create') {
                                if ($this->context->getMethod() != 'POST') {
                                    $allow[] = 'POST';
                                    goto not_online_form_packages_create;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\ApiController::createPackagesAction',  '_route' => 'online_form_packages_create',);
                            }
                            not_online_form_packages_create:

                        }

                        // online_form_results
                        if (preg_match('#^/management/online/api/results(?:/(?P<id>[^/]++))?$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_online_form_results;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'online_form_results')), array (  '_format' => 'js',  'id' => NULL,  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\ApiController::getResultsAction',));
                        }
                        not_online_form_results:

                    }

                }

                if (0 === strpos($pathinfo, '/management/online/form')) {
                    // online_form
                    if (rtrim($pathinfo, '/') === '/management/online/form') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_online_form;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'online_form');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\FormController::indexAction',  '_route' => 'online_form',);
                    }
                    not_online_form:

                    // online_form_new
                    if ($pathinfo === '/management/online/form/new') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_online_form_new;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\FormController::newAction',  '_route' => 'online_form_new',);
                    }
                    not_online_form_new:

                    // online_form_edit
                    if (preg_match('#^/management/online/form/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_online_form_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'online_form_edit')), array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\FormController::editAction',));
                    }
                    not_online_form_edit:

                    // online_form_delete
                    if (preg_match('#^/management/online/form/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_online_form_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'online_form_delete')), array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\FormController::deleteAction',));
                    }
                    not_online_form_delete:

                }

                if (0 === strpos($pathinfo, '/management/online/invite')) {
                    // invite
                    if (rtrim($pathinfo, '/') === '/management/online/invite') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_invite;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'invite');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\InviteController::indexAction',  '_route' => 'invite',);
                    }
                    not_invite:

                    // invite_form
                    if ($pathinfo === '/management/online/invite/form') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_invite_form;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\InviteController::formAction',  '_route' => 'invite_form',);
                    }
                    not_invite_form:

                }

                if (0 === strpos($pathinfo, '/management/online/api/poll')) {
                    // online_poll_list
                    if (0 === strpos($pathinfo, '/management/online/api/poll/questions/list') && preg_match('#^/management/online/api/poll/questions/list/(?P<id>[^/]++)/(?P<payerId>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_online_poll_list;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'online_poll_list')), array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\PollController::listAction',));
                    }
                    not_online_poll_list:

                    // online_poll_config
                    if ($pathinfo === '/management/online/api/poll/config') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_online_poll_config;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\PollController::configAction',  '_route' => 'online_poll_config',);
                    }
                    not_online_poll_config:

                    // online_poll_js
                    if ($pathinfo === '/management/online/api/poll/js/main') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_online_poll_js;
                        }

                        return array (  '_format' => 'js',  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\PollController::pollAction',  '_route' => 'online_poll_js',);
                    }
                    not_online_poll_js:

                }

                // online_form_show
                if ($pathinfo === '/management/online/search/form') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_online_form_show;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\OnlineBundle\\Controller\\SearchController::formAction',  '_route' => 'online_form_show',);
                }
                not_online_form_show:

            }

            if (0 === strpos($pathinfo, '/management/channelmanager')) {
                if (0 === strpos($pathinfo, '/management/channelmanager/booking')) {
                    // booking
                    if (rtrim($pathinfo, '/') === '/management/channelmanager/booking') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_booking;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'booking');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\BookingController::indexAction',  '_route' => 'booking',);
                    }
                    not_booking:

                    // booking_save
                    if ($pathinfo === '/management/channelmanager/booking/') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_booking_save;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\BookingController::saveAction',  '_route' => 'booking_save',);
                    }
                    not_booking_save:

                    // booking_room
                    if ($pathinfo === '/management/channelmanager/booking/room') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_booking_room;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\BookingController::roomAction',  '_route' => 'booking_room',);
                    }
                    not_booking_room:

                    // booking_tariff
                    if ($pathinfo === '/management/channelmanager/booking/tariff') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_booking_tariff;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\BookingController::tariffAction',  '_route' => 'booking_tariff',);
                    }
                    not_booking_tariff:

                    // booking_service
                    if ($pathinfo === '/management/channelmanager/booking/service') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_booking_service;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\BookingController::serviceAction',  '_route' => 'booking_service',);
                    }
                    not_booking_service:

                }

                // channel_manager_notifications
                if (0 === strpos($pathinfo, '/management/channelmanager/package/notifications') && preg_match('#^/management/channelmanager/package/notifications/(?P<name>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('POST', 'GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('POST', 'GET', 'HEAD'));
                        goto not_channel_manager_notifications;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'channel_manager_notifications')), array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\ChannelManagerController::packageNotificationsAction',));
                }
                not_channel_manager_notifications:

                // channel_manager_logs
                if ($pathinfo === '/management/channelmanager/logs') {
                    if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                        goto not_channel_manager_logs;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\ChannelManagerController::logsAction',  '_route' => 'channel_manager_logs',);
                }
                not_channel_manager_logs:

                // channel_manager_sync
                if ($pathinfo === '/management/channelmanager/sync') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_channel_manager_sync;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\ChannelManagerController::syncAction',  '_route' => 'channel_manager_sync',);
                }
                not_channel_manager_sync:

                if (0 === strpos($pathinfo, '/management/channelmanager/hotelinn')) {
                    // hotelinn
                    if (rtrim($pathinfo, '/') === '/management/channelmanager/hotelinn') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_hotelinn;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'hotelinn');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\HotelinnController::indexAction',  '_route' => 'hotelinn',);
                    }
                    not_hotelinn:

                    // hotelinn_save
                    if ($pathinfo === '/management/channelmanager/hotelinn/') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_hotelinn_save;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\HotelinnController::saveAction',  '_route' => 'hotelinn_save',);
                    }
                    not_hotelinn_save:

                    // hotelinn_room
                    if ($pathinfo === '/management/channelmanager/hotelinn/room') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_hotelinn_room;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\HotelinnController::roomAction',  '_route' => 'hotelinn_room',);
                    }
                    not_hotelinn_room:

                    // hotelinn_tariff
                    if ($pathinfo === '/management/channelmanager/hotelinn/tariff') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_hotelinn_tariff;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\HotelinnController::tariffAction',  '_route' => 'hotelinn_tariff',);
                    }
                    not_hotelinn_tariff:

                    // hotelinn_service
                    if ($pathinfo === '/management/channelmanager/hotelinn/service') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_hotelinn_service;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\HotelinnController::serviceAction',  '_route' => 'hotelinn_service',);
                    }
                    not_hotelinn_service:

                }

                if (0 === strpos($pathinfo, '/management/channelmanager/myallocator')) {
                    // channels
                    if (rtrim($pathinfo, '/') === '/management/channelmanager/myallocator') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_channels;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'channels');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\MyallocatorController::indexAction',  '_route' => 'channels',);
                    }
                    not_channels:

                    // channels_save
                    if ($pathinfo === '/management/channelmanager/myallocator/') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_channels_save;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\MyallocatorController::saveAction',  '_route' => 'channels_save',);
                    }
                    not_channels_save:

                    // channels_user_unlink
                    if ($pathinfo === '/management/channelmanager/myallocator/user/unlink') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_channels_user_unlink;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\MyallocatorController::userUnlinkAction',  '_route' => 'channels_user_unlink',);
                    }
                    not_channels_user_unlink:

                    // channels_room
                    if ($pathinfo === '/management/channelmanager/myallocator/room') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_channels_room;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\MyallocatorController::roomAction',  '_route' => 'channels_room',);
                    }
                    not_channels_room:

                    // channels_tariff
                    if ($pathinfo === '/management/channelmanager/myallocator/tariff') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_channels_tariff;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\MyallocatorController::tariffAction',  '_route' => 'channels_tariff',);
                    }
                    not_channels_tariff:

                    // channels_service
                    if ($pathinfo === '/management/channelmanager/myallocator/service') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_channels_service;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\MyallocatorController::serviceAction',  '_route' => 'channels_service',);
                    }
                    not_channels_service:

                    // channels_vendor_set
                    if (0 === strpos($pathinfo, '/management/channelmanager/myallocator/vendor/set') && preg_match('#^/management/channelmanager/myallocator/vendor/set/(?P<user>[^/]++)/(?P<password>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_channels_vendor_set;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'channels_vendor_set')), array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\MyallocatorController::vendorAction',));
                    }
                    not_channels_vendor_set:

                }

                if (0 === strpos($pathinfo, '/management/channelmanager/o')) {
                    if (0 === strpos($pathinfo, '/management/channelmanager/oktogo')) {
                        // oktogo
                        if (rtrim($pathinfo, '/') === '/management/channelmanager/oktogo') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_oktogo;
                            }

                            if (substr($pathinfo, -1) !== '/') {
                                return $this->redirect($pathinfo.'/', 'oktogo');
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OktogoController::indexAction',  '_route' => 'oktogo',);
                        }
                        not_oktogo:

                        // oktogo_save
                        if ($pathinfo === '/management/channelmanager/oktogo/') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_oktogo_save;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OktogoController::saveAction',  '_route' => 'oktogo_save',);
                        }
                        not_oktogo_save:

                        if (0 === strpos($pathinfo, '/management/channelmanager/oktogo/room')) {
                            // oktogo_room
                            if ($pathinfo === '/management/channelmanager/oktogo/room') {
                                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                    $allow = array_merge($allow, array('GET', 'HEAD'));
                                    goto not_oktogo_room;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OktogoController::roomAction',  '_route' => 'oktogo_room',);
                            }
                            not_oktogo_room:

                            // oktogo_room_save
                            if ($pathinfo === '/management/channelmanager/oktogo/room') {
                                if ($this->context->getMethod() != 'POST') {
                                    $allow[] = 'POST';
                                    goto not_oktogo_room_save;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OktogoController::roomSaveAction',  '_route' => 'oktogo_room_save',);
                            }
                            not_oktogo_room_save:

                            // oktogo_room_sync
                            if ($pathinfo === '/management/channelmanager/oktogo/room/sync') {
                                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                    $allow = array_merge($allow, array('GET', 'HEAD'));
                                    goto not_oktogo_room_sync;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OktogoController::roomSyncAction',  '_route' => 'oktogo_room_sync',);
                            }
                            not_oktogo_room_sync:

                        }

                        if (0 === strpos($pathinfo, '/management/channelmanager/oktogo/tariff')) {
                            // oktogo_tariff_sync
                            if ($pathinfo === '/management/channelmanager/oktogo/tariff/sync') {
                                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                    $allow = array_merge($allow, array('GET', 'HEAD'));
                                    goto not_oktogo_tariff_sync;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OktogoController::tariffSyncAction',  '_route' => 'oktogo_tariff_sync',);
                            }
                            not_oktogo_tariff_sync:

                            // oktogo_tariff
                            if ($pathinfo === '/management/channelmanager/oktogo/tariff') {
                                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                    $allow = array_merge($allow, array('GET', 'HEAD'));
                                    goto not_oktogo_tariff;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OktogoController::tariffAction',  '_route' => 'oktogo_tariff',);
                            }
                            not_oktogo_tariff:

                            // oktogo_tariff_save
                            if ($pathinfo === '/management/channelmanager/oktogo/tariff') {
                                if ($this->context->getMethod() != 'POST') {
                                    $allow[] = 'POST';
                                    goto not_oktogo_tariff_save;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OktogoController::tariffSaveAction',  '_route' => 'oktogo_tariff_save',);
                            }
                            not_oktogo_tariff_save:

                        }

                    }

                    if (0 === strpos($pathinfo, '/management/channelmanager/ostrovok')) {
                        // ostrovok
                        if (rtrim($pathinfo, '/') === '/management/channelmanager/ostrovok') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_ostrovok;
                            }

                            if (substr($pathinfo, -1) !== '/') {
                                return $this->redirect($pathinfo.'/', 'ostrovok');
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OstrovokController::indexAction',  '_route' => 'ostrovok',);
                        }
                        not_ostrovok:

                        // ostrovok_save
                        if ($pathinfo === '/management/channelmanager/ostrovok/') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_ostrovok_save;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OstrovokController::saveAction',  '_route' => 'ostrovok_save',);
                        }
                        not_ostrovok_save:

                        // ostrovok_room
                        if ($pathinfo === '/management/channelmanager/ostrovok/room') {
                            if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                                goto not_ostrovok_room;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OstrovokController::roomAction',  '_route' => 'ostrovok_room',);
                        }
                        not_ostrovok_room:

                        // ostrovok_tariff
                        if ($pathinfo === '/management/channelmanager/ostrovok/tariff') {
                            if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                                goto not_ostrovok_tariff;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OstrovokController::tariffAction',  '_route' => 'ostrovok_tariff',);
                        }
                        not_ostrovok_tariff:

                        // ostrovok_service
                        if ($pathinfo === '/management/channelmanager/ostrovok/service') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_ostrovok_service;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\OstrovokController::serviceAction',  '_route' => 'ostrovok_service',);
                        }
                        not_ostrovok_service:

                    }

                }

                if (0 === strpos($pathinfo, '/management/channelmanager/vashotel')) {
                    // vashotel
                    if (rtrim($pathinfo, '/') === '/management/channelmanager/vashotel') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_vashotel;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'vashotel');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\VashotelController::indexAction',  '_route' => 'vashotel',);
                    }
                    not_vashotel:

                    // vashotel_save
                    if ($pathinfo === '/management/channelmanager/vashotel/') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_vashotel_save;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\VashotelController::saveAction',  '_route' => 'vashotel_save',);
                    }
                    not_vashotel_save:

                    // vashotel_room
                    if ($pathinfo === '/management/channelmanager/vashotel/room') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_vashotel_room;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\VashotelController::roomAction',  '_route' => 'vashotel_room',);
                    }
                    not_vashotel_room:

                    // vashotel_tariff
                    if ($pathinfo === '/management/channelmanager/vashotel/tariff') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_vashotel_tariff;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\VashotelController::tariffAction',  '_route' => 'vashotel_tariff',);
                    }
                    not_vashotel_tariff:

                    // vashotel_service
                    if ($pathinfo === '/management/channelmanager/vashotel/service') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_vashotel_service;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\ChannelManagerBundle\\Controller\\VashotelController::serviceAction',  '_route' => 'vashotel_service',);
                    }
                    not_vashotel_service:

                }

            }

        }

        if (0 === strpos($pathinfo, '/cash')) {
            // cash
            if (rtrim($pathinfo, '/') === '/cash') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_cash;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'cash');
                }

                return array (  '_controller' => 'MBH\\Bundle\\CashBundle\\Controller\\CashController::indexAction',  '_route' => 'cash',);
            }
            not_cash:

            // cash_json
            if ($pathinfo === '/cash/json') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_cash_json;
                }

                return array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\CashBundle\\Controller\\CashController::jsonAction',  '_route' => 'cash_json',);
            }
            not_cash_json:

            // cash_1c_export
            if (0 === strpos($pathinfo, '/cash/export/1c') && preg_match('#^/cash/export/1c(?:/(?P<method>[^/]++))?$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_cash_1c_export;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'cash_1c_export')), array (  'method' => NULL,  '_controller' => 'MBH\\Bundle\\CashBundle\\Controller\\CashController::export1cAction',));
            }
            not_cash_1c_export:

            // cash_new
            if ($pathinfo === '/cash/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                    goto not_cash_new;
                }

                return array (  '_controller' => 'MBH\\Bundle\\CashBundle\\Controller\\CashController::newAction',  '_route' => 'cash_new',);
            }
            not_cash_new:

            // cash_edit
            if (preg_match('#^/cash/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                    goto not_cash_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'cash_edit')), array (  '_controller' => 'MBH\\Bundle\\CashBundle\\Controller\\CashController::editAction',));
            }
            not_cash_edit:

            // cash_delete
            if (preg_match('#^/cash/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_cash_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'cash_delete')), array (  '_controller' => 'MBH\\Bundle\\CashBundle\\Controller\\CashController::deleteAction',));
            }
            not_cash_delete:

            // cash_confirm
            if (preg_match('#^/cash/(?P<id>[^/]++)/confirm$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_cash_confirm;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'cash_confirm')), array (  '_controller' => 'MBH\\Bundle\\CashBundle\\Controller\\CashController::confirmAction',));
            }
            not_cash_confirm:

            // cash_card_money
            if (preg_match('#^/cash/(?P<id>[^/]++)/card/money$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_cash_card_money;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'cash_card_money')), array (  '_controller' => 'MBH\\Bundle\\CashBundle\\Controller\\CashController::getMoneyFromCardAction',));
            }
            not_cash_card_money:

            // cash_pay
            if (preg_match('#^/cash/(?P<id>[^/]++)/pay$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_cash_pay;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'cash_pay')), array (  '_controller' => 'MBH\\Bundle\\CashBundle\\Controller\\CashController::payAction',));
            }
            not_cash_pay:

            // get_payers
            if ($pathinfo === '/cash/payers') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_get_payers;
                }

                return array (  '_controller' => 'MBH\\Bundle\\CashBundle\\Controller\\CashController::getPayersAction',  '_route' => 'get_payers',);
            }
            not_get_payers:

        }

        // _welcome
        if (rtrim($pathinfo, '/') === '') {
            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', '_welcome');
            }

            return array (  '_controller' => 'MBH\\Bundle\\BaseBundle\\Controller\\WelcomeController::indexAction',  '_route' => '_welcome',);
        }

        if (0 === strpos($pathinfo, '/p')) {
            if (0 === strpos($pathinfo, '/package')) {
                if (0 === strpos($pathinfo, '/package/analytics')) {
                    // analytics
                    if (rtrim($pathinfo, '/') === '/package/analytics') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_analytics;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'analytics');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\AnalyticsController::indexAction',  '_route' => 'analytics',);
                    }
                    not_analytics:

                    // analytics_choose
                    if ($pathinfo === '/package/analytics/choose') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_analytics_choose;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\AnalyticsController::chooseAction',  '_route' => 'analytics_choose',);
                    }
                    not_analytics_choose:

                    if (0 === strpos($pathinfo, '/package/analytics/sales_')) {
                        // analytics_sales_services
                        if ($pathinfo === '/package/analytics/sales_services') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_analytics_sales_services;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\AnalyticsController::salesServicesAction',  '_route' => 'analytics_sales_services',);
                        }
                        not_analytics_sales_services:

                        // analytics_sales_cash_documents
                        if ($pathinfo === '/package/analytics/sales_cash_documents') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_analytics_sales_cash_documents;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\AnalyticsController::salesCashDocumentsAction',  '_route' => 'analytics_sales_cash_documents',);
                        }
                        not_analytics_sales_cash_documents:

                    }

                    // analytics_hotel_occupancy
                    if ($pathinfo === '/package/analytics/hotel_occupancy') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_analytics_hotel_occupancy;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\AnalyticsController::hotelOccupancyAction',  '_route' => 'analytics_hotel_occupancy',);
                    }
                    not_analytics_hotel_occupancy:

                    if (0 === strpos($pathinfo, '/package/analytics/sales_')) {
                        // analytics_sales_managers
                        if ($pathinfo === '/package/analytics/sales_managers') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_analytics_sales_managers;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\AnalyticsController::salesManagersAction',  '_route' => 'analytics_sales_managers',);
                        }
                        not_analytics_sales_managers:

                        // analytics_sales_sources
                        if ($pathinfo === '/package/analytics/sales_sources') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_analytics_sales_sources;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\AnalyticsController::salesSourcesAction',  '_route' => 'analytics_sales_sources',);
                        }
                        not_analytics_sales_sources:

                        // analytics_sales_packages
                        if ($pathinfo === '/package/analytics/sales_packages') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_analytics_sales_packages;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\AnalyticsController::salesPackagesAction',  '_route' => 'analytics_sales_packages',);
                        }
                        not_analytics_sales_packages:

                        // analytics_sales_cash
                        if ($pathinfo === '/package/analytics/sales_cash') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_analytics_sales_cash;
                            }

                            return array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\AnalyticsController::salesCashAction',  '_route' => 'analytics_sales_cash',);
                        }
                        not_analytics_sales_cash:

                    }

                }

                // order_documents
                if (preg_match('#^/package/(?P<id>[^/]++)/documents/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                        goto not_order_documents;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'order_documents')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\DocumentsController::indexAction',));
                }
                not_order_documents:

                if (0 === strpos($pathinfo, '/package/document')) {
                    // order_remove_document
                    if (preg_match('#^/package/document/(?P<id>[^/]++)/(?P<packageId>[^/]++)/(?P<name>[^/]++)/remove$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_order_remove_document;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'order_remove_document')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\DocumentsController::removeAction',));
                    }
                    not_order_remove_document:

                    // order_document_view
                    if (preg_match('#^/package/document/(?P<name>[^/]++)/view(?:/(?P<download>[^/]++))?$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_order_document_view;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'order_document_view')), array (  'download' => 0,  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\DocumentsController::viewAction',));
                    }
                    not_order_document_view:

                    // order_document_edit
                    if (preg_match('#^/package/document/(?P<id>[^/]++)/edit/(?P<packageId>[^/]++)/(?P<name>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                            goto not_order_document_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'order_document_edit')), array (  'download' => 0,  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\DocumentsController::editAction',));
                    }
                    not_order_document_edit:

                    if (0 === strpos($pathinfo, '/package/document/generated')) {
                        // package_pdf
                        if (preg_match('#^/package/document/generated/(?P<id>[^/]++)/(?P<type>confirmation|confirmation_en|registration_card|fms_form_5|evidence|form_1_g|receipt|act|xls_notice|bill)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                                goto not_package_pdf;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_pdf')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\GeneratedDocumentController::actPdfAction',));
                        }
                        not_package_pdf:

                        // document_modal_form
                        if (preg_match('#^/package/document/generated/(?P<id>[^/]++)/modal_form/(?P<type>[^/]++)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_document_modal_form;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'document_modal_form')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\GeneratedDocumentController::documentModalFormAction',));
                        }
                        not_document_modal_form:

                        // stamp
                        if (0 === strpos($pathinfo, '/package/document/generated/stamp') && preg_match('#^/package/document/generated/stamp/(?P<id>[^/\\.]++)\\.jpg$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_stamp;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'stamp')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\GeneratedDocumentController::stampAction',));
                        }
                        not_stamp:

                    }

                }

                if (0 === strpos($pathinfo, '/package/or')) {
                    if (0 === strpos($pathinfo, '/package/order')) {
                        // package_order_cash_delete
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/cash/(?P<cash>[^/]++)/delete/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_package_order_cash_delete;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_cash_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::cashDeleteAction',));
                        }
                        not_package_order_cash_delete:

                        // package_order_cash
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/cash/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                                goto not_package_order_cash;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_cash')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::cashAction',));
                        }
                        not_package_order_cash:

                        // package_order_tourist_edit
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/tourist/edit/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_package_order_tourist_edit;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_tourist_edit')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::touristEditAction',));
                        }
                        not_package_order_tourist_edit:

                        // package_order_organization_edit
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/organization/edit/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_package_order_organization_edit;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_organization_edit')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::organizationEditAction',));
                        }
                        not_package_order_organization_edit:

                        // package_order_tourist_update
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/tourist/update/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'PUT') {
                                $allow[] = 'PUT';
                                goto not_package_order_tourist_update;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_tourist_update')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::touristUpdateAction',));
                        }
                        not_package_order_tourist_update:

                        // package_order_organization_update
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/organization/update/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'PUT') {
                                $allow[] = 'PUT';
                                goto not_package_order_organization_update;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_organization_update')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::organizationUpdateAction',));
                        }
                        not_package_order_organization_update:

                        // package_order_tourist_delete
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/tourist/delete/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_package_order_tourist_delete;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_tourist_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::touristDeleteAction',));
                        }
                        not_package_order_tourist_delete:

                        // package_order_organization_delete
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/organization/delete/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_package_order_organization_delete;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_organization_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::organizationDeleteAction',));
                        }
                        not_package_order_organization_delete:

                        // package_order_edit
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/edit/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_package_order_edit;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_edit')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::editAction',));
                        }
                        not_package_order_edit:

                        // package_order_update
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/update/(?P<packageId>[^/]++)$#s', $pathinfo, $matches)) {
                            if ($this->context->getMethod() != 'PUT') {
                                $allow[] = 'PUT';
                                goto not_package_order_update;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_update')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::updateAction',));
                        }
                        not_package_order_update:

                        // package_order_delete
                        if (preg_match('#^/package/order/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_package_order_delete;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_order_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrderController::deleteAction',));
                        }
                        not_package_order_delete:

                    }

                    if (0 === strpos($pathinfo, '/package/organizations')) {
                        // organizations
                        if (0 === strpos($pathinfo, '/package/organizations/list') && preg_match('#^/package/organizations/list(?:/(?P<type>[^/]++))?$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_organizations;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'organizations')), array (  'type' => 'contragents',  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrganizationController::indexAction',));
                        }
                        not_organizations:

                        // organization_json
                        if ($pathinfo === '/package/organizations/json') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_organization_json;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrganizationController::organizationJsonAction',  '_route' => 'organization_json',);
                        }
                        not_organization_json:

                        // create_organization
                        if ($pathinfo === '/package/organizations/create') {
                            if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                                goto not_create_organization;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrganizationController::createAction',  '_route' => 'create_organization',);
                        }
                        not_create_organization:

                        // organization_edit
                        if (preg_match('#^/package/organizations/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                                goto not_organization_edit;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'organization_edit')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrganizationController::editAction',));
                        }
                        not_organization_edit:

                        // organization_delete
                        if (preg_match('#^/package/organizations/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_organization_delete;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'organization_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrganizationController::deleteAction',));
                        }
                        not_organization_delete:

                        // organization_json_list
                        if ($pathinfo === '/package/organizations/json/list') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_organization_json_list;
                            }

                            return array (  'id' => NULL,  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\OrganizationController::organizationJsonListAction',  '_route' => 'organization_json_list',);
                        }
                        not_organization_json_list:

                    }

                }

                // package
                if (rtrim($pathinfo, '/') === '/package') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'package');
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::indexAction',  '_route' => 'package',);
                }
                not_package:

                // package_json
                if ($pathinfo === '/package/json') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_json;
                    }

                    return array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::jsonAction',  '_route' => 'package_json',);
                }
                not_package_json:

                // package_edit
                if (preg_match('#^/package/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_edit')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::editAction',));
                }
                not_package_edit:

                // package_update
                if (preg_match('#^/package/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_package_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_update')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::updateAction',));
                }
                not_package_update:

                // package_new
                if ($pathinfo === '/package/new') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_new;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::newAction',  '_route' => 'package_new',);
                }
                not_package_new:

                // package_guest
                if (preg_match('#^/package/(?P<id>[^/]++)/guest$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                        goto not_package_guest;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_guest')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::guestAction',));
                }
                not_package_guest:

                // package_guest_delete
                if (preg_match('#^/package/(?P<id>[^/]++)/guest/(?P<touristId>[^/]++)/delete$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_guest_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_guest_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::guestDeleteAction',));
                }
                not_package_guest_delete:

                // package_service
                if (preg_match('#^/package/(?P<id>[^/]++)/services$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                        goto not_package_service;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_service')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::serviceAction',));
                }
                not_package_service:

                // package_service_edit
                if (preg_match('#^/package/(?P<id>[^/]++)/service/(?P<serviceId>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                        goto not_package_service_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_service_edit')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::serviceEditAction',));
                }
                not_package_service_edit:

                // package_service_delete
                if (preg_match('#^/package/(?P<id>[^/]++)/service/(?P<serviceId>[^/]++)/delete$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_service_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_service_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::serviceDeleteAction',));
                }
                not_package_service_delete:

                // package_accommodation_set
                if (preg_match('#^/package/(?P<id>[^/]++)/accommodation/set/(?P<roomId>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_accommodation_set;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_accommodation_set')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::accommodationSetAction',));
                }
                not_package_accommodation_set:

                // package_relocation
                if (preg_match('#^/package/(?P<id>[^/]++)/relocation/(?P<date>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_relocation;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_relocation')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::relocationAction',));
                }
                not_package_relocation:

                // package_accommodation
                if (preg_match('#^/package/(?P<id>[^/]++)/accommodation$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                        goto not_package_accommodation;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_accommodation')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::accommodationAction',));
                }
                not_package_accommodation:

                // package_accommodation_delete
                if (preg_match('#^/package/(?P<id>[^/]++)/accommodation/delete$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_accommodation_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_accommodation_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::accommodationDeleteAction',));
                }
                not_package_accommodation_delete:

                // package_delete
                if (preg_match('#^/package/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::deleteAction',));
                }
                not_package_delete:

                // package_unlock
                if (preg_match('#^/package/(?P<id>[^/]++)/unlock$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_unlock;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_unlock')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::unlockAction',));
                }
                not_package_unlock:

                // package_lock
                if (preg_match('#^/package/(?P<id>[^/]++)/lock$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_package_lock;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_lock')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::lockAction',));
                }
                not_package_lock:

                if (0 === strpos($pathinfo, '/package/getPackageJson')) {
                    // getPackageJsonById
                    if (0 === strpos($pathinfo, '/package/getPackageJsonById') && preg_match('#^/package/getPackageJsonById(?:/(?P<id>[^/]++))?$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'getPackageJsonById')), array (  'id' => NULL,  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::getPackageJsonByIdAction',));
                    }

                    // getPackageJsonSearch
                    if ($pathinfo === '/package/getPackageJsonSearch') {
                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\PackageController::getPackageJsonSearchAction',  '_route' => 'getPackageJsonSearch',);
                    }

                }

                if (0 === strpos($pathinfo, '/package/report')) {
                    if (0 === strpos($pathinfo, '/package/report/porter')) {
                        // report_porter
                        if (preg_match('#^/package/report/porter(?:/(?P<type>lives|arrivals|out))?$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_porter;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'report_porter')), array (  'type' => 'lives',  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::porterAction',));
                        }
                        not_report_porter:

                        // report_porter_table
                        if ($pathinfo === '/package/report/porter/table') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_porter_table;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::porterTableAction',  '_route' => 'report_porter_table',);
                        }
                        not_report_porter_table:

                    }

                    if (0 === strpos($pathinfo, '/package/report/users')) {
                        // report_users
                        if ($pathinfo === '/package/report/users/index') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_users;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::userAction',  '_route' => 'report_users',);
                        }
                        not_report_users:

                        // report_users_table
                        if ($pathinfo === '/package/report/users/table') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_users_table;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::userTableAction',  '_route' => 'report_users_table',);
                        }
                        not_report_users_table:

                    }

                    if (0 === strpos($pathinfo, '/package/report/accommodation')) {
                        // report_accommodation
                        if ($pathinfo === '/package/report/accommodation/index') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_accommodation;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::accommodationAction',  '_route' => 'report_accommodation',);
                        }
                        not_report_accommodation:

                        // report_accommodation_table
                        if ($pathinfo === '/package/report/accommodation/table') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_accommodation_table;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::accommodationTableAction',  '_route' => 'report_accommodation_table',);
                        }
                        not_report_accommodation_table:

                    }

                    // report_fms
                    if ($pathinfo === '/package/report/fms') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_report_fms;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::fmsAction',  '_route' => 'report_fms',);
                    }
                    not_report_fms:

                    if (0 === strpos($pathinfo, '/package/report/polls')) {
                        // report_polls
                        if ($pathinfo === '/package/report/polls') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_polls;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::pollsAction',  '_route' => 'report_polls',);
                        }
                        not_report_polls:

                        // report_polls_view
                        if (preg_match('#^/package/report/polls/(?P<id>[^/]++)/view$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_polls_view;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'report_polls_view')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::pollsViewAction',));
                        }
                        not_report_polls_view:

                    }

                    if (0 === strpos($pathinfo, '/package/report/roomtypes')) {
                        // report_room_types
                        if ($pathinfo === '/package/report/roomtypes') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_room_types;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::roomTypesAction',  '_route' => 'report_room_types',);
                        }
                        not_report_room_types:

                        // report_room_types_table
                        if ($pathinfo === '/package/report/roomtypes_table') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_room_types_table;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::roomTypesTableAction',  '_route' => 'report_room_types_table',);
                        }
                        not_report_room_types_table:

                    }

                    // report_set_room_status
                    if ($pathinfo === '/package/report/set_room_status') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_report_set_room_status;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::setRoomStatusAction',  '_route' => 'report_set_room_status',);
                    }
                    not_report_set_room_status:

                    // report_invite
                    if ($pathinfo === '/package/report/invite') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_report_invite;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::inviteAction',  '_route' => 'report_invite',);
                    }
                    not_report_invite:

                    if (0 === strpos($pathinfo, '/package/report/filling')) {
                        // report_filling
                        if ($pathinfo === '/package/report/filling') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_filling;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::fillingAction',  '_route' => 'report_filling',);
                        }
                        not_report_filling:

                        // report_filling_table
                        if ($pathinfo === '/package/report/filling/table') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_filling_table;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::fillingTableAction',  '_route' => 'report_filling_table',);
                        }
                        not_report_filling_table:

                    }

                    if (0 === strpos($pathinfo, '/package/report/work_shift')) {
                        // report_work_shift
                        if ($pathinfo === '/package/report/work_shift') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_report_work_shift;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::workShiftAction',  '_route' => 'report_work_shift',);
                        }
                        not_report_work_shift:

                        // report_work_shift_list
                        if ($pathinfo === '/package/report/work_shift_table') {
                            if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                                goto not_report_work_shift_list;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::workShiftListAction',  '_route' => 'report_work_shift_list',);
                        }
                        not_report_work_shift_list:

                    }

                    // report_work_shift_table
                    if ($pathinfo === '/package/report/get_work_shift') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_report_work_shift_table;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ReportController::workShiftTableAction',  '_route' => 'report_work_shift_table',);
                    }
                    not_report_work_shift_table:

                }

                if (0 === strpos($pathinfo, '/package/se')) {
                    if (0 === strpos($pathinfo, '/package/search')) {
                        // package_search
                        if (rtrim($pathinfo, '/') === '/package/search') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_package_search;
                            }

                            if (substr($pathinfo, -1) !== '/') {
                                return $this->redirect($pathinfo.'/', 'package_search');
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\SearchController::indexAction',  '_route' => 'package_search',);
                        }
                        not_package_search:

                        // package_search_results
                        if ($pathinfo === '/package/search/results') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_package_search_results;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\SearchController::resultsAction',  '_route' => 'package_search_results',);
                        }
                        not_package_search_results:

                    }

                    if (0 === strpos($pathinfo, '/package/service')) {
                        // service_list
                        if ($pathinfo === '/package/service/index') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_service_list;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ServiceController::indexAction',  '_route' => 'service_list',);
                        }
                        not_service_list:

                        // ajax_service_list
                        if ($pathinfo === '/package/service/ajax') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_ajax_service_list;
                            }

                            return array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\ServiceController::ajaxListAction',  '_route' => 'ajax_service_list',);
                        }
                        not_ajax_service_list:

                    }

                }

                if (0 === strpos($pathinfo, '/package/management/source')) {
                    // package_source
                    if (rtrim($pathinfo, '/') === '/package/management/source') {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_package_source;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'package_source');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\SourceController::indexAction',  '_route' => 'package_source',);
                    }
                    not_package_source:

                    // package_source_edit
                    if (preg_match('#^/package/management/source/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_package_source_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_source_edit')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\SourceController::editAction',));
                    }
                    not_package_source_edit:

                    // package_source_update
                    if (preg_match('#^/package/management/source/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_package_source_update;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_source_update')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\SourceController::updateAction',));
                    }
                    not_package_source_update:

                    // package_source_delete
                    if (preg_match('#^/package/management/source/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_package_source_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'package_source_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\SourceController::deleteAction',));
                    }
                    not_package_source_delete:

                }

                if (0 === strpos($pathinfo, '/package/tourist')) {
                    // tourist
                    if (rtrim($pathinfo, '/') === '/package/tourist') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_tourist;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'tourist');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::indexAction',  '_route' => 'tourist',);
                    }
                    not_tourist:

                    // tourist_json
                    if ($pathinfo === '/package/tourist/json') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_tourist_json;
                        }

                        return array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::jsonAction',  '_route' => 'tourist_json',);
                    }
                    not_tourist_json:

                    // tourist_new
                    if ($pathinfo === '/package/tourist/new') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_tourist_new;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::newAction',  '_route' => 'tourist_new',);
                    }
                    not_tourist_new:

                    if (0 === strpos($pathinfo, '/package/tourist/create')) {
                        // tourist_create_ajax
                        if ($pathinfo === '/package/tourist/create/ajax') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_tourist_create_ajax;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::createAjaxAction',  '_route' => 'tourist_create_ajax',);
                        }
                        not_tourist_create_ajax:

                        // tourist_create
                        if ($pathinfo === '/package/tourist/create') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_tourist_create;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::createAction',  '_route' => 'tourist_create',);
                        }
                        not_tourist_create:

                    }

                    // tourist_update
                    if (preg_match('#^/package/tourist/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_tourist_update;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tourist_update')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::updateAction',));
                    }
                    not_tourist_update:

                    // tourist_edit
                    if (preg_match('#^/package/tourist/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_tourist_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tourist_edit')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::editAction',));
                    }
                    not_tourist_edit:

                    // tourist_edit_document
                    if (preg_match('#^/package/tourist/(?P<id>[^/]++)/edit/document$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                            goto not_tourist_edit_document;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tourist_edit_document')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::editDocumentAction',));
                    }
                    not_tourist_edit_document:

                    // tourist_edit_visa
                    if (preg_match('#^/package/tourist/(?P<id>[^/]++)/edit/visa$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                            goto not_tourist_edit_visa;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tourist_edit_visa')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::editVisaAction',));
                    }
                    not_tourist_edit_visa:

                    // tourist_edit_unwelcome
                    if (preg_match('#^/package/tourist/(?P<id>[^/]++)/edit/unwelcome$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                            goto not_tourist_edit_unwelcome;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tourist_edit_unwelcome')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::editUnwelcomeAction',));
                    }
                    not_tourist_edit_unwelcome:

                    // tourist_delete_unwelcome
                    if (preg_match('#^/package/tourist/(?P<id>[^/]++)/delete/unwelcome$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                            goto not_tourist_delete_unwelcome;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tourist_delete_unwelcome')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::deleteUnwelcomeAction',));
                    }
                    not_tourist_delete_unwelcome:

                    // get_json_regions
                    if ($pathinfo === '/package/tourist/regions') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_get_json_regions;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::ajaxRegionAction',  '_route' => 'get_json_regions',);
                    }
                    not_get_json_regions:

                    if (0 === strpos($pathinfo, '/package/tourist/authority_organ')) {
                        // authority_organ_json_list
                        if ($pathinfo === '/package/tourist/authority_organ_json_list') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_authority_organ_json_list;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::authorityOrganListAction',  '_route' => 'authority_organ_json_list',);
                        }
                        not_authority_organ_json_list:

                        // ajax_authority_organ
                        if (preg_match('#^/package/tourist/authority_organ/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_ajax_authority_organ;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'ajax_authority_organ')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::authorityOrganAction',));
                        }
                        not_ajax_authority_organ:

                    }

                    // tourist_edit_address
                    if (preg_match('#^/package/tourist/(?P<id>[^/]++)/edit/address$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                            goto not_tourist_edit_address;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tourist_edit_address')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::editAddressAction',));
                    }
                    not_tourist_edit_address:

                    // tourist_delete
                    if (preg_match('#^/package/tourist/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_tourist_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tourist_delete')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::deleteAction',));
                    }
                    not_tourist_delete:

                    if (0 === strpos($pathinfo, '/package/tourist/get')) {
                        // json_tourist
                        if (preg_match('#^/package/tourist/get/(?P<id>[^/]++)/json$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_json_tourist;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'json_tourist')), array (  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::jsonEntryAction',));
                        }
                        not_json_tourist:

                        // ajax_tourists
                        if (preg_match('#^/package/tourist/get(?:/(?P<id>[^/]++))?$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_ajax_tourists;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'ajax_tourists')), array (  'id' => NULL,  '_controller' => 'MBH\\Bundle\\PackageBundle\\Controller\\TouristController::ajaxListAction',));
                        }
                        not_ajax_tourists:

                    }

                }

            }

            if (0 === strpos($pathinfo, '/price')) {
                if (0 === strpos($pathinfo, '/price/overview')) {
                    // room_overview
                    if (rtrim($pathinfo, '/') === '/price/overview') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_room_overview;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'room_overview');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\OverviewController::indexAction',  '_route' => 'room_overview',);
                    }
                    not_room_overview:

                    // room_overview_table
                    if ($pathinfo === '/price/overview/table') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_room_overview_table;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\OverviewController::tableAction',  '_route' => 'room_overview_table',);
                    }
                    not_room_overview_table:

                }

                if (0 === strpos($pathinfo, '/price/pr')) {
                    if (0 === strpos($pathinfo, '/price/price_cache')) {
                        // price_cache_overview
                        if (rtrim($pathinfo, '/') === '/price/price_cache') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_price_cache_overview;
                            }

                            if (substr($pathinfo, -1) !== '/') {
                                return $this->redirect($pathinfo.'/', 'price_cache_overview');
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\PriceCacheController::indexAction',  '_route' => 'price_cache_overview',);
                        }
                        not_price_cache_overview:

                        // price_cache_overview_table
                        if ($pathinfo === '/price/price_cache/table') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_price_cache_overview_table;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\PriceCacheController::tableAction',  '_route' => 'price_cache_overview_table',);
                        }
                        not_price_cache_overview_table:

                        // price_cache_overview_save
                        if ($pathinfo === '/price/price_cache/save') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_price_cache_overview_save;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\PriceCacheController::saveAction',  '_route' => 'price_cache_overview_save',);
                        }
                        not_price_cache_overview_save:

                        if (0 === strpos($pathinfo, '/price/price_cache/generator')) {
                            // price_cache_generator
                            if ($pathinfo === '/price/price_cache/generator') {
                                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                    $allow = array_merge($allow, array('GET', 'HEAD'));
                                    goto not_price_cache_generator;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\PriceCacheController::generatorAction',  '_route' => 'price_cache_generator',);
                            }
                            not_price_cache_generator:

                            // price_cache_generator_save
                            if ($pathinfo === '/price/price_cache/generator/save') {
                                if ($this->context->getMethod() != 'POST') {
                                    $allow[] = 'POST';
                                    goto not_price_cache_generator_save;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\PriceCacheController::generatorSaveAction',  '_route' => 'price_cache_generator_save',);
                            }
                            not_price_cache_generator_save:

                        }

                    }

                    if (0 === strpos($pathinfo, '/price/promotions')) {
                        // promotions
                        if (rtrim($pathinfo, '/') === '/price/promotions') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_promotions;
                            }

                            if (substr($pathinfo, -1) !== '/') {
                                return $this->redirect($pathinfo.'/', 'promotions');
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\PromotionController::indexAction',  '_route' => 'promotions',);
                        }
                        not_promotions:

                        // promotion_new
                        if ($pathinfo === '/price/promotions/new') {
                            if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                                goto not_promotion_new;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\PromotionController::newAction',  '_route' => 'promotion_new',);
                        }
                        not_promotion_new:

                        // promotion_edit
                        if (preg_match('#^/price/promotions(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                                goto not_promotion_edit;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'promotion_edit')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\PromotionController::editAction',));
                        }
                        not_promotion_edit:

                        // promotion_delete
                        if (preg_match('#^/price/promotions/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'promotion_delete')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\PromotionController::deleteAction',));
                        }

                    }

                }

                if (0 === strpos($pathinfo, '/price/r')) {
                    if (0 === strpos($pathinfo, '/price/restriction')) {
                        // restriction_in_out_json
                        if ($pathinfo === '/price/restriction/in/out/json') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_restriction_in_out_json;
                            }

                            return array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RestrictionController::inOutJsonAction',  '_route' => 'restriction_in_out_json',);
                        }
                        not_restriction_in_out_json:

                        // restriction_overview
                        if (rtrim($pathinfo, '/') === '/price/restriction') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_restriction_overview;
                            }

                            if (substr($pathinfo, -1) !== '/') {
                                return $this->redirect($pathinfo.'/', 'restriction_overview');
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RestrictionController::indexAction',  '_route' => 'restriction_overview',);
                        }
                        not_restriction_overview:

                        // restriction_overview_table
                        if ($pathinfo === '/price/restriction/table') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_restriction_overview_table;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RestrictionController::tableAction',  '_route' => 'restriction_overview_table',);
                        }
                        not_restriction_overview_table:

                        // restriction_overview_save
                        if ($pathinfo === '/price/restriction/save') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_restriction_overview_save;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RestrictionController::saveAction',  '_route' => 'restriction_overview_save',);
                        }
                        not_restriction_overview_save:

                        if (0 === strpos($pathinfo, '/price/restriction/generator')) {
                            // restriction_generator
                            if ($pathinfo === '/price/restriction/generator') {
                                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                    $allow = array_merge($allow, array('GET', 'HEAD'));
                                    goto not_restriction_generator;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RestrictionController::generatorAction',  '_route' => 'restriction_generator',);
                            }
                            not_restriction_generator:

                            // restriction_generator_save
                            if ($pathinfo === '/price/restriction/generator/save') {
                                if ($this->context->getMethod() != 'POST') {
                                    $allow[] = 'POST';
                                    goto not_restriction_generator_save;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RestrictionController::generatorSaveAction',  '_route' => 'restriction_generator_save',);
                            }
                            not_restriction_generator_save:

                        }

                    }

                    if (0 === strpos($pathinfo, '/price/room_cache')) {
                        // room_cache_overview
                        if (rtrim($pathinfo, '/') === '/price/room_cache') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_room_cache_overview;
                            }

                            if (substr($pathinfo, -1) !== '/') {
                                return $this->redirect($pathinfo.'/', 'room_cache_overview');
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RoomCacheController::indexAction',  '_route' => 'room_cache_overview',);
                        }
                        not_room_cache_overview:

                        // room_cache_overview_graph
                        if ($pathinfo === '/price/room_cache/graph') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_room_cache_overview_graph;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RoomCacheController::graphAction',  '_route' => 'room_cache_overview_graph',);
                        }
                        not_room_cache_overview_graph:

                        // room_cache_overview_table
                        if ($pathinfo === '/price/room_cache/table') {
                            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'HEAD'));
                                goto not_room_cache_overview_table;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RoomCacheController::tableAction',  '_route' => 'room_cache_overview_table',);
                        }
                        not_room_cache_overview_table:

                        // room_cache_overview_save
                        if ($pathinfo === '/price/room_cache/save') {
                            if ($this->context->getMethod() != 'POST') {
                                $allow[] = 'POST';
                                goto not_room_cache_overview_save;
                            }

                            return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RoomCacheController::saveAction',  '_route' => 'room_cache_overview_save',);
                        }
                        not_room_cache_overview_save:

                        if (0 === strpos($pathinfo, '/price/room_cache/generator')) {
                            // room_cache_generator
                            if ($pathinfo === '/price/room_cache/generator') {
                                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                                    $allow = array_merge($allow, array('GET', 'HEAD'));
                                    goto not_room_cache_generator;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RoomCacheController::generatorAction',  '_route' => 'room_cache_generator',);
                            }
                            not_room_cache_generator:

                            // room_cache_generator_save
                            if ($pathinfo === '/price/room_cache/generator/save') {
                                if ($this->context->getMethod() != 'POST') {
                                    $allow[] = 'POST';
                                    goto not_room_cache_generator_save;
                                }

                                return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\RoomCacheController::generatorSaveAction',  '_route' => 'room_cache_generator_save',);
                            }
                            not_room_cache_generator_save:

                        }

                    }

                }

                if (0 === strpos($pathinfo, '/price/service')) {
                    // price_service_category
                    if (rtrim($pathinfo, '/') === '/price/service') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_price_service_category;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'price_service_category');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::indexAction',  '_route' => 'price_service_category',);
                    }
                    not_price_service_category:

                    // price_service_category_save_prices
                    if ($pathinfo === '/price/service/') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_price_service_category_save_prices;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::savePricesAction',  '_route' => 'price_service_category_save_prices',);
                    }
                    not_price_service_category_save_prices:

                    // price_service_category_entry_new
                    if (preg_match('#^/price/service/(?P<id>[^/]++)/new/entry$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_price_service_category_entry_new;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'price_service_category_entry_new')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::newEntryAction',));
                    }
                    not_price_service_category_entry_new:

                    // price_service_category_entry_create
                    if (preg_match('#^/price/service/(?P<id>[^/]++)/create/entry$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_price_service_category_entry_create;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'price_service_category_entry_create')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::createEntryAction',));
                    }
                    not_price_service_category_entry_create:

                    // price_service_category_entry_edit
                    if (preg_match('#^/price/service/(?P<id>[^/]++)/edit/entry$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_price_service_category_entry_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'price_service_category_entry_edit')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::editEntryAction',));
                    }
                    not_price_service_category_entry_edit:

                    // price_service_category_entry_update
                    if (preg_match('#^/price/service/(?P<id>[^/]++)/update/entry$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_price_service_category_entry_update;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'price_service_category_entry_update')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::updateEntryAction',));
                    }
                    not_price_service_category_entry_update:

                    // price_service_category_new
                    if ($pathinfo === '/price/service/new') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_price_service_category_new;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::newAction',  '_route' => 'price_service_category_new',);
                    }
                    not_price_service_category_new:

                    // price_service_category_create
                    if ($pathinfo === '/price/service/create') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_price_service_category_create;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::createAction',  '_route' => 'price_service_category_create',);
                    }
                    not_price_service_category_create:

                    // price_service_category_edit
                    if (preg_match('#^/price/service/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_price_service_category_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'price_service_category_edit')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::editAction',));
                    }
                    not_price_service_category_edit:

                    // price_service_category_update
                    if (preg_match('#^/price/service/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_price_service_category_update;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'price_service_category_update')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::updateAction',));
                    }
                    not_price_service_category_update:

                    // price_service_category_delete
                    if (preg_match('#^/price/service/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_price_service_category_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'price_service_category_delete')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::deleteAction',));
                    }
                    not_price_service_category_delete:

                    // price_service_category_entry_delete
                    if (preg_match('#^/price/service/(?P<id>[^/]++)/entry/delete$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_price_service_category_entry_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'price_service_category_entry_delete')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\ServiceController::deleteEntryAction',));
                    }
                    not_price_service_category_entry_delete:

                }

                if (0 === strpos($pathinfo, '/price/management/tariff')) {
                    // tariff
                    if (rtrim($pathinfo, '/') === '/price/management/tariff') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_tariff;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'tariff');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::indexAction',  '_route' => 'tariff',);
                    }
                    not_tariff:

                    // tariff_extend
                    if (preg_match('#^/price/management/tariff/(?P<id>[^/]++)/inherit$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_tariff_extend;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tariff_extend')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::extendAction',));
                    }
                    not_tariff_extend:

                    // tariff_copy
                    if (preg_match('#^/price/management/tariff/(?P<id>[^/]++)/copy$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_tariff_copy;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tariff_copy')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::copyAction',));
                    }
                    not_tariff_copy:

                    // tariff_new
                    if ($pathinfo === '/price/management/tariff/new') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_tariff_new;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::newAction',  '_route' => 'tariff_new',);
                    }
                    not_tariff_new:

                    // tariff_create
                    if ($pathinfo === '/price/management/tariff/create') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_tariff_create;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::createAction',  '_route' => 'tariff_create',);
                    }
                    not_tariff_create:

                    // tariff_update
                    if (preg_match('#^/price/management/tariff/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_tariff_update;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tariff_update')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::updateAction',));
                    }
                    not_tariff_update:

                    // tariff_edit
                    if (preg_match('#^/price/management/tariff/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_tariff_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tariff_edit')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::editAction',));
                    }
                    not_tariff_edit:

                    if (0 === strpos($pathinfo, '/price/management/tariff/edit')) {
                        // tariff_promotions_edit
                        if (preg_match('#^/price/management/tariff/edit/(?P<id>[^/]++)/promotions$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                                goto not_tariff_promotions_edit;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'tariff_promotions_edit')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::editPromotionsAction',));
                        }
                        not_tariff_promotions_edit:

                        // tariff_inheritance_edit
                        if (preg_match('#^/price/management/tariff/edit/(?P<id>[^/]++)/inheritance$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                                goto not_tariff_inheritance_edit;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'tariff_inheritance_edit')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::editInheritanceAction',));
                        }
                        not_tariff_inheritance_edit:

                        // tariff_services_edit
                        if (preg_match('#^/price/management/tariff/edit/(?P<id>[^/]++)/services$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                                goto not_tariff_services_edit;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'tariff_services_edit')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::editServicesAction',));
                        }
                        not_tariff_services_edit:

                    }

                    // tariff_delete
                    if (preg_match('#^/price/management/tariff/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_tariff_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'tariff_delete')), array (  '_controller' => 'MBH\\Bundle\\PriceBundle\\Controller\\TariffController::deleteAction',));
                    }
                    not_tariff_delete:

                }

            }

        }

        // export_csv
        if (0 === strpos($pathinfo, '/export/csv') && preg_match('#^/export/csv/(?P<repositoryName>[^/]++)$#s', $pathinfo, $matches)) {
            return $this->mergeDefaults(array_replace($matches, array('_route' => 'export_csv')), array (  '_controller' => 'MBH\\Bundle\\BaseBundle\\Controller\\ExportController::csvAction',));
        }

        if (0 === strpos($pathinfo, '/management/group')) {
            // group
            if (rtrim($pathinfo, '/') === '/management/group') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_group;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'group');
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\GroupController::indexAction',  '_route' => 'group',);
            }
            not_group:

            // group_new
            if ($pathinfo === '/management/group/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_group_new;
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\GroupController::newAction',  '_route' => 'group_new',);
            }
            not_group_new:

            // group_create
            if ($pathinfo === '/management/group/create') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_group_create;
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\GroupController::createAction',  '_route' => 'group_create',);
            }
            not_group_create:

            // group_edit
            if (preg_match('#^/management/group/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_group_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'group_edit')), array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\GroupController::editAction',));
            }
            not_group_edit:

            // group_update
            if (preg_match('#^/management/group/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_group_update;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'group_update')), array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\GroupController::updateAction',));
            }
            not_group_update:

            // group_delete
            if (preg_match('#^/management/group/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_group_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'group_delete')), array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\GroupController::deleteAction',));
            }
            not_group_delete:

        }

        if (0 === strpos($pathinfo, '/user/profile')) {
            // user_profile
            if ($pathinfo === '/user/profile') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_user_profile;
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\ProfileController::profileAction',  '_route' => 'user_profile',);
            }
            not_user_profile:

            // user_profile_update
            if ($pathinfo === '/user/profile') {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_user_profile_update;
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\ProfileController::profileUpdateAction',  '_route' => 'user_profile_update',);
            }
            not_user_profile_update:

        }

        if (0 === strpos($pathinfo, '/management/user')) {
            // user
            if (rtrim($pathinfo, '/') === '/management/user') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_user;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'user');
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\UserController::indexAction',  '_route' => 'user',);
            }
            not_user:

            // user_new
            if ($pathinfo === '/management/user/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_user_new;
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\UserController::newAction',  '_route' => 'user_new',);
            }
            not_user_new:

            // user_create
            if ($pathinfo === '/management/user/create') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_user_create;
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\UserController::createAction',  '_route' => 'user_create',);
            }
            not_user_create:

            // user_edit
            if (preg_match('#^/management/user/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_user_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_edit')), array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\UserController::editAction',));
            }
            not_user_edit:

            // user_document_edit
            if (preg_match('#^/management/user/(?P<id>[^/]++)/edit/document$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                    goto not_user_document_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_document_edit')), array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\UserController::editDocumentAction',));
            }
            not_user_document_edit:

            // user_security_edit
            if (preg_match('#^/management/user/(?P<id>[^/]++)/edit/security$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                    goto not_user_security_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_security_edit')), array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\UserController::editSecurityAction',));
            }
            not_user_security_edit:

            // user_address_edit
            if (preg_match('#^/management/user/(?P<id>[^/]++)/edit/address$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                    goto not_user_address_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_address_edit')), array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\UserController::editAddressAction',));
            }
            not_user_address_edit:

            // user_update
            if (preg_match('#^/management/user/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_user_update;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_update')), array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\UserController::updateAction',));
            }
            not_user_update:

            // user_delete
            if (preg_match('#^/management/user/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_user_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'user_delete')), array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\UserController::deleteAction',));
            }
            not_user_delete:

        }

        if (0 === strpos($pathinfo, '/work-shift')) {
            // work_shift
            if (rtrim($pathinfo, '/') === '/work-shift') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_work_shift;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'work_shift');
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\WorkShiftController::indexAction',  '_route' => 'work_shift',);
            }
            not_work_shift:

            // work_shift_wait
            if ($pathinfo === '/work-shift/wait') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_work_shift_wait;
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\WorkShiftController::waitAction',  '_route' => 'work_shift_wait',);
            }
            not_work_shift_wait:

            // work_shift_start
            if (0 === strpos($pathinfo, '/work-shift/start') && preg_match('#^/work\\-shift/start/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_work_shift_start;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'work_shift_start')), array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\WorkShiftController::startAction',));
            }
            not_work_shift_start:

            // work_shift_new
            if ($pathinfo === '/work-shift/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_work_shift_new;
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\WorkShiftController::newAction',  '_route' => 'work_shift_new',);
            }
            not_work_shift_new:

            // work_shift_lock
            if ($pathinfo === '/work-shift/lock') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_work_shift_lock;
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\WorkShiftController::lockAction',  '_route' => 'work_shift_lock',);
            }
            not_work_shift_lock:

            // work_shift_ajax_close
            if ($pathinfo === '/work-shift/close') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_work_shift_ajax_close;
                }

                return array (  '_controller' => 'MBH\\Bundle\\UserBundle\\Controller\\WorkShiftController::ajaxCloseAction',  '_route' => 'work_shift_ajax_close',);
            }
            not_work_shift_ajax_close:

        }

        if (0 === strpos($pathinfo, '/user/log')) {
            if (0 === strpos($pathinfo, '/user/login')) {
                // fos_user_security_login
                if ($pathinfo === '/user/login') {
                    if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                        goto not_fos_user_security_login;
                    }

                    return array (  '_controller' => 'FOS\\UserBundle\\Controller\\SecurityController::loginAction',  '_route' => 'fos_user_security_login',);
                }
                not_fos_user_security_login:

                // fos_user_security_check
                if ($pathinfo === '/user/login_check') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_fos_user_security_check;
                    }

                    return array (  '_controller' => 'FOS\\UserBundle\\Controller\\SecurityController::checkAction',  '_route' => 'fos_user_security_check',);
                }
                not_fos_user_security_check:

            }

            // fos_user_security_logout
            if ($pathinfo === '/user/logout') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_fos_user_security_logout;
                }

                return array (  '_controller' => 'FOS\\UserBundle\\Controller\\SecurityController::logoutAction',  '_route' => 'fos_user_security_logout',);
            }
            not_fos_user_security_logout:

        }

        // fos_js_routing_js
        if (0 === strpos($pathinfo, '/js/routing') && preg_match('#^/js/routing(?:\\.(?P<_format>js|json))?$#s', $pathinfo, $matches)) {
            return $this->mergeDefaults(array_replace($matches, array('_route' => 'fos_js_routing_js')), array (  '_controller' => 'fos_js_routing.controller:indexAction',  '_format' => 'js',));
        }

        if (0 === strpos($pathinfo, '/management/hotel')) {
            // hotel_not_found
            if ($pathinfo === '/management/hotel/notfound') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_hotel_not_found;
                }

                return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::notFoundAction',  '_route' => 'hotel_not_found',);
            }
            not_hotel_not_found:

            // hotel_select
            if (preg_match('#^/management/hotel/(?P<id>[^/]++)/select$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_hotel_select;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'hotel_select')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::selectHotelAction',));
            }
            not_hotel_select:

            // hotel
            if (rtrim($pathinfo, '/') === '/management/hotel') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_hotel;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'hotel');
                }

                return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::indexAction',  '_route' => 'hotel',);
            }
            not_hotel:

            // hotel_new
            if ($pathinfo === '/management/hotel/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_hotel_new;
                }

                return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::newAction',  '_route' => 'hotel_new',);
            }
            not_hotel_new:

            // hotel_create
            if ($pathinfo === '/management/hotel/create') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_hotel_create;
                }

                return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::createAction',  '_route' => 'hotel_create',);
            }
            not_hotel_create:

            // hotel_update
            if (preg_match('#^/management/hotel/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_hotel_update;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'hotel_update')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::updateAction',));
            }
            not_hotel_update:

            // hotel_edit
            if (preg_match('#^/management/hotel/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_hotel_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'hotel_edit')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::editAction',));
            }
            not_hotel_edit:

            // hotel_delete_logo
            if (preg_match('#^/management/hotel/(?P<id>[^/]++)/delete/logo$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_hotel_delete_logo;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'hotel_delete_logo')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::deleteLogoAction',));
            }
            not_hotel_delete_logo:

            // hotel_edit_extended
            if (preg_match('#^/management/hotel/(?P<id>[^/]++)/edit/extended$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_hotel_edit_extended;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'hotel_edit_extended')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::extendedAction',));
            }
            not_hotel_edit_extended:

            // hotel_edit_extended_save
            if (preg_match('#^/management/hotel/(?P<id>[^/]++)/edit/extended$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_hotel_edit_extended_save;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'hotel_edit_extended_save')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::extendedUpdateAction',));
            }
            not_hotel_edit_extended_save:

            // hotel_city
            if (0 === strpos($pathinfo, '/management/hotel/city') && preg_match('#^/management/hotel/city(?:/(?P<id>[^/]++))?$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_hotel_city;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'hotel_city')), array (  'id' => NULL,  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::cityAction',));
            }
            not_hotel_city:

            // hotel_delete
            if (preg_match('#^/management/hotel/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_hotel_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'hotel_delete')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HotelController::deleteAction',));
            }
            not_hotel_delete:

            if (0 === strpos($pathinfo, '/management/hotel/housing')) {
                // housing
                if (rtrim($pathinfo, '/') === '/management/hotel/housing') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'housing');
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HousingController::indexAction',  '_route' => 'housing',);
                }

                if (0 === strpos($pathinfo, '/management/hotel/housing/new')) {
                    // housing_new
                    if ($pathinfo === '/management/hotel/housing/new') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_housing_new;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HousingController::newAction',  '_route' => 'housing_new',);
                    }
                    not_housing_new:

                    // housing_create
                    if ($pathinfo === '/management/hotel/housing/new') {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_housing_create;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HousingController::createAction',  '_route' => 'housing_create',);
                    }
                    not_housing_create:

                }

                // housing_edit
                if (0 === strpos($pathinfo, '/management/hotel/housing/edit') && preg_match('#^/management/hotel/housing/edit/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_housing_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'housing_edit')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HousingController::editAction',));
                }
                not_housing_edit:

                // housing_update
                if (0 === strpos($pathinfo, '/management/hotel/housing/update') && preg_match('#^/management/hotel/housing/update/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_housing_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'housing_update')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HousingController::updateAction',));
                }
                not_housing_update:

                // housing_delete
                if (0 === strpos($pathinfo, '/management/hotel/housing/delete') && preg_match('#^/management/hotel/housing/delete/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'housing_delete')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\HousingController::deleteAction',));
                }

            }

            if (0 === strpos($pathinfo, '/management/hotel/room')) {
                // room_type_room_json
                if (preg_match('#^/management/hotel/room/(?P<id>[^/]++)/room/?$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_room_type_room_json;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'room_type_room_json');
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_type_room_json')), array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomController::jsonListAction',));
                }
                not_room_type_room_json:

                // room_new
                if (preg_match('#^/management/hotel/room/(?P<id>[^/]++)/new/?$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_room_new;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'room_new');
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_new')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomController::newAction',));
                }
                not_room_new:

                // room_create
                if (preg_match('#^/management/hotel/room/(?P<id>[^/]++)/room/new/$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_room_create;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_create')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomController::createAction',));
                }
                not_room_create:

                // room_edit
                if (preg_match('#^/management/hotel/room/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_room_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_edit')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomController::editAction',));
                }
                not_room_edit:

                // room_update
                if (preg_match('#^/management/hotel/room/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_room_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_update')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomController::updateAction',));
                }
                not_room_update:

                // room_delete
                if (preg_match('#^/management/hotel/room/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_room_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_delete')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomController::deleteAction',));
                }
                not_room_delete:

                // generate_rooms
                if (preg_match('#^/management/hotel/room/(?P<id>[^/]++)/generate/?$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_generate_rooms;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'generate_rooms');
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'generate_rooms')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomController::generateAction',));
                }
                not_generate_rooms:

                // generate_rooms_process
                if (preg_match('#^/management/hotel/room/(?P<id>[^/]++)/generate/$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_generate_rooms_process;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'generate_rooms_process')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomController::generateProcessAction',));
                }
                not_generate_rooms_process:

                if (0 === strpos($pathinfo, '/management/hotel/room_type/category')) {
                    // room_type_category
                    if (rtrim($pathinfo, '/') === '/management/hotel/room_type/category') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_room_type_category;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'room_type_category');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeCategoryController::indexAction',  '_route' => 'room_type_category',);
                    }
                    not_room_type_category:

                    // room_type_category_new
                    if ($pathinfo === '/management/hotel/room_type/category/new') {
                        if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                            goto not_room_type_category_new;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeCategoryController::newAction',  '_route' => 'room_type_category_new',);
                    }
                    not_room_type_category_new:

                    // room_type_category_edit
                    if (0 === strpos($pathinfo, '/management/hotel/room_type/category/edit') && preg_match('#^/management/hotel/room_type/category/edit/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_room_type_category_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_type_category_edit')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeCategoryController::editAction',));
                    }
                    not_room_type_category_edit:

                    // room_type_category_delete
                    if (0 === strpos($pathinfo, '/management/hotel/room_type/category/delete') && preg_match('#^/management/hotel/room_type/category/delete/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_room_type_category_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_type_category_delete')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeCategoryController::deleteAction',));
                    }
                    not_room_type_category_delete:

                }

                if (0 === strpos($pathinfo, '/management/hotel/roomtype')) {
                    // room_type
                    if (rtrim($pathinfo, '/') === '/management/hotel/roomtype') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_room_type;
                        }

                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'room_type');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeController::indexAction',  '_route' => 'room_type',);
                    }
                    not_room_type:

                    // room_type_new
                    if ($pathinfo === '/management/hotel/roomtype/new') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_room_type_new;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeController::newAction',  '_route' => 'room_type_new',);
                    }
                    not_room_type_new:

                    // room_type_create
                    if ($pathinfo === '/management/hotel/roomtype/create') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_room_type_create;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeController::createAction',  '_route' => 'room_type_create',);
                    }
                    not_room_type_create:

                    // room_type_image_delete
                    if (preg_match('#^/management/hotel/roomtype/(?P<id>[^/]++)/image/(?P<imageId>[^/]++)/delete$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_room_type_image_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_type_image_delete')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeController::imageDelete',));
                    }
                    not_room_type_image_delete:

                    // room_type_edit
                    if (preg_match('#^/management/hotel/roomtype/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_room_type_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_type_edit')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeController::editAction',));
                    }
                    not_room_type_edit:

                    // room_type_update
                    if (preg_match('#^/management/hotel/roomtype/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'PUT') {
                            $allow[] = 'PUT';
                            goto not_room_type_update;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_type_update')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeController::updateAction',));
                    }
                    not_room_type_update:

                    // room_type_task_edit
                    if (preg_match('#^/management/hotel/roomtype/(?P<id>[^/]++)/edit/tasks$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_room_type_task_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_type_task_edit')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeController::editAutoTasksAction',));
                    }
                    not_room_type_task_edit:

                    // room_type_delete
                    if (preg_match('#^/management/hotel/roomtype/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_room_type_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_type_delete')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeController::deleteAction',));
                    }
                    not_room_type_delete:

                    if (0 === strpos($pathinfo, '/management/hotel/roomtype/image')) {
                        // room_type_image_make_main
                        if (preg_match('#^/management/hotel/roomtype/image/(?P<imageId>[^/]++)/main/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_type_image_make_main')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeController::makeMainImageRoomTypeAction',));
                        }

                        // room_type_image_edit
                        if (0 === strpos($pathinfo, '/management/hotel/roomtype/images') && preg_match('#^/management/hotel/roomtype/images/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                            if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                                $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                                goto not_room_type_image_edit;
                            }

                            return $this->mergeDefaults(array_replace($matches, array('_route' => 'room_type_image_edit')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\RoomTypeController::editImagesAction',));
                        }
                        not_room_type_image_edit:

                    }

                }

            }

            if (0 === strpos($pathinfo, '/management/hotel/tasktype')) {
                if (0 === strpos($pathinfo, '/management/hotel/tasktype/category')) {
                    // task_type_category_new
                    if ($pathinfo === '/management/hotel/tasktype/category/new') {
                        if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                            goto not_task_type_category_new;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskTypeCategoryController::newAction',  '_route' => 'task_type_category_new',);
                    }
                    not_task_type_category_new:

                    // task_type_category_edit
                    if (preg_match('#^/management/hotel/tasktype/category/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_task_type_category_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_type_category_edit')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskTypeCategoryController::editAction',));
                    }
                    not_task_type_category_edit:

                    // task_type_category_delete
                    if (preg_match('#^/management/hotel/tasktype/category/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_task_type_category_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_type_category_delete')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskTypeCategoryController::deleteAction',));
                    }
                    not_task_type_category_delete:

                }

                // tasktype
                if (preg_match('#^/management/hotel/tasktype(?:/(?P<category>\\w*))?$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                        goto not_tasktype;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'tasktype')), array (  'category' => NULL,  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskTypeController::indexAction',));
                }
                not_tasktype:

                // tasktype_edit
                if (preg_match('#^/management/hotel/tasktype/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                        goto not_tasktype_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'tasktype_edit')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskTypeController::editAction',));
                }
                not_tasktype_edit:

                // tasktype_delete
                if (preg_match('#^/management/hotel/tasktype/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_tasktype_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'tasktype_delete')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskTypeController::deleteAction',));
                }
                not_tasktype_delete:

            }

        }

        if (0 === strpos($pathinfo, '/task')) {
            // task
            if (rtrim($pathinfo, '/') === '/task') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_task;
                }

                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', 'task');
                }

                return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskController::indexAction',  '_route' => 'task',);
            }
            not_task:

            // task_json
            if ($pathinfo === '/task/json_list') {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_task_json;
                }

                return array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskController::jsonListAction',  '_route' => 'task_json',);
            }
            not_task_json:

            // task_change_status
            if (0 === strpos($pathinfo, '/task/change_status') && preg_match('#^/task/change_status/(?P<id>[^/]++)/(?P<status>[^/]++)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_task_change_status;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_change_status')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskController::changeStatusAction',));
            }
            not_task_change_status:

            // task_new
            if ($pathinfo === '/task/new') {
                if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                    goto not_task_new;
                }

                return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskController::newAction',  '_route' => 'task_new',);
            }
            not_task_new:

            // task_edit
            if (0 === strpos($pathinfo, '/task/edit') && preg_match('#^/task/edit/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'PUT', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'PUT', 'HEAD'));
                    goto not_task_edit;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_edit')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskController::editAction',));
            }
            not_task_edit:

            // task_delete
            if (preg_match('#^/task/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_task_delete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'task_delete')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskController::deleteAction',));
            }
            not_task_delete:

            // ajax_task_details
            if (preg_match('#^/task/(?P<id>[^/]++)/ajax$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_ajax_task_details;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'ajax_task_details')), array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskController::ajaxTaskDerailsAction',));
            }
            not_ajax_task_details:

            // task_ajax_total_my_open
            if ($pathinfo === '/task/ajax/my_total') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_task_ajax_total_my_open;
                }

                return array (  '_controller' => 'MBH\\Bundle\\HotelBundle\\Controller\\TaskController::ajaxMyOpenTaskTotal',  '_route' => 'task_ajax_total_my_open',);
            }
            not_task_ajax_total_my_open:

        }

        if (0 === strpos($pathinfo, '/media/cache/resolve')) {
            // liip_imagine_filter_runtime
            if (preg_match('#^/media/cache/resolve/(?P<filter>[A-z0-9_\\-]*)/rc/(?P<hash>[^/]++)/(?P<path>.+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_liip_imagine_filter_runtime;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'liip_imagine_filter_runtime')), array (  '_controller' => 'liip_imagine.controller:filterRuntimeAction',));
            }
            not_liip_imagine_filter_runtime:

            // liip_imagine_filter
            if (preg_match('#^/media/cache/resolve/(?P<filter>[A-z0-9_\\-]*)/(?P<path>.+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_liip_imagine_filter;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'liip_imagine_filter')), array (  '_controller' => 'liip_imagine.controller:filterAction',));
            }
            not_liip_imagine_filter:

        }

        // vega_export
        if (0 === strpos($pathinfo, '/vega') && preg_match('#^/vega/(?P<id>[^/]++)/export$#s', $pathinfo, $matches)) {
            if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                $allow = array_merge($allow, array('GET', 'HEAD'));
                goto not_vega_export;
            }

            return $this->mergeDefaults(array_replace($matches, array('_route' => 'vega_export')), array (  '_controller' => 'MBH\\Bundle\\VegaBundle\\Controller\\DefaultController::exportAction',));
        }
        not_vega_export:

        if (0 === strpos($pathinfo, '/restaurant')) {
            if (0 === strpos($pathinfo, '/restaurant/dish')) {
                if (0 === strpos($pathinfo, '/restaurant/dishmenu')) {
                    // restaurant_dishmenu_category
                    if (rtrim($pathinfo, '/') === '/restaurant/dishmenu') {
                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'restaurant_dishmenu_category');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishMenuController::indexAction',  '_route' => 'restaurant_dishmenu_category',);
                    }

                    // restaurant_dishmenu_category_new
                    if ($pathinfo === '/restaurant/dishmenu/newcategory') {
                        return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishMenuController::newCategoryAction',  '_route' => 'restaurant_dishmenu_category_new',);
                    }

                    // restaurant_dishmenu_category_edit
                    if (preg_match('#^/restaurant/dishmenu/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_restaurant_dishmenu_category_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_dishmenu_category_edit')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishMenuController::editCategoryAction',));
                    }
                    not_restaurant_dishmenu_category_edit:

                    // restaurant_dishmenu_category_delete
                    if (preg_match('#^/restaurant/dishmenu/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_restaurant_dishmenu_category_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_dishmenu_category_delete')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishMenuController::deleteCategoryAction',));
                    }
                    not_restaurant_dishmenu_category_delete:

                    // restaurant_dishmenu_item_new
                    if (preg_match('#^/restaurant/dishmenu/(?P<id>[^/]++)/new/dishmenuitem$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_dishmenu_item_new')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishMenuController::newItemAction',));
                    }

                    // restaurant_dishmenu_item_edit
                    if (preg_match('#^/restaurant/dishmenu/(?P<id>[^/]++)/edit/dishmenuitem$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_restaurant_dishmenu_item_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_dishmenu_item_edit')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishMenuController::editItemAction',));
                    }
                    not_restaurant_dishmenu_item_edit:

                    // restaurant_dishmenu_item_delete
                    if (preg_match('#^/restaurant/dishmenu/(?P<id>[^/]++)/delete/dishmenuitem$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_dishmenu_item_delete')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishMenuController::deleteItemAction',));
                    }

                    // restaurant_dishmenu_save_prices
                    if ($pathinfo === '/restaurant/dishmenu/quicksave') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_restaurant_dishmenu_save_prices;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishMenuController::savePricesAction',  '_route' => 'restaurant_dishmenu_save_prices',);
                    }
                    not_restaurant_dishmenu_save_prices:

                }

                if (0 === strpos($pathinfo, '/restaurant/dishorder')) {
                    // restaurant_dishorder_list
                    if (rtrim($pathinfo, '/') === '/restaurant/dishorder') {
                        if (substr($pathinfo, -1) !== '/') {
                            return $this->redirect($pathinfo.'/', 'restaurant_dishorder_list');
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishOrderController::indexAction',  '_route' => 'restaurant_dishorder_list',);
                    }

                    // restaurant_dishorder_quicksave
                    if ($pathinfo === '/restaurant/dishorder/quicksave') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_restaurant_dishorder_quicksave;
                        }

                        return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishOrderController::quickSaveOrderAction',  '_route' => 'restaurant_dishorder_quicksave',);
                    }
                    not_restaurant_dishorder_quicksave:

                    // restaurant_dishorder_new
                    if ($pathinfo === '/restaurant/dishorder/new') {
                        return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishOrderController::newOrderAction',  '_route' => 'restaurant_dishorder_new',);
                    }

                    // restaurant_dishorder_edit
                    if (preg_match('#^/restaurant/dishorder/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                            goto not_restaurant_dishorder_edit;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_dishorder_edit')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishOrderController::editOrderAction',));
                    }
                    not_restaurant_dishorder_edit:

                    // restaurant_dishorder_delete
                    if (preg_match('#^/restaurant/dishorder/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_dishorder_delete')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishOrderController::deleteOrderAction',));
                    }

                    // restaurant_dishorder_showfreezed
                    if (preg_match('#^/restaurant/dishorder/(?P<id>[^/]++)/showfreezed$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_dishorder_showfreezed')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishOrderController::showFreezedOrderAction',));
                    }

                    // restaurant_dishorder_freeze
                    if (preg_match('#^/restaurant/dishorder/(?P<id>[^/]++)/freeze$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_dishorder_freeze')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishOrderController::freezOrderAction',));
                    }

                    // restaurant_json
                    if ($pathinfo === '/restaurant/dishorder/json') {
                        return array (  '_format' => 'json',  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\DishOrderController::jsonAction',  '_route' => 'restaurant_json',);
                    }

                }

            }

            if (0 === strpos($pathinfo, '/restaurant/ingredients')) {
                // restaurant_ingredient_category
                if (rtrim($pathinfo, '/') === '/restaurant/ingredients') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_restaurant_ingredient_category;
                    }

                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'restaurant_ingredient_category');
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::indexAction',  '_route' => 'restaurant_ingredient_category',);
                }
                not_restaurant_ingredient_category:

                // restaurant_category_save_prices
                if ($pathinfo === '/restaurant/ingredients/quicksave') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_restaurant_category_save_prices;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::savePricesAction',  '_route' => 'restaurant_category_save_prices',);
                }
                not_restaurant_category_save_prices:

                // restaurant_ingredient_category_new
                if ($pathinfo === '/restaurant/ingredients/newcategory') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_restaurant_ingredient_category_new;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::newCategoryAction',  '_route' => 'restaurant_ingredient_category_new',);
                }
                not_restaurant_ingredient_category_new:

                // restaurant_ingredient_category_create
                if ($pathinfo === '/restaurant/ingredients/createcategory') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_restaurant_ingredient_category_create;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::createCategoryAction',  '_route' => 'restaurant_ingredient_category_create',);
                }
                not_restaurant_ingredient_category_create:

                // restaurant_ingredient_category_edit
                if (preg_match('#^/restaurant/ingredients/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_restaurant_ingredient_category_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_ingredient_category_edit')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::editCategoryAction',));
                }
                not_restaurant_ingredient_category_edit:

                // restaurant_ingredient_category_update
                if (preg_match('#^/restaurant/ingredients/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_restaurant_ingredient_category_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_ingredient_category_update')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::updateCategoryAction',));
                }
                not_restaurant_ingredient_category_update:

                // restaurant_ingredient_category_delete
                if (preg_match('#^/restaurant/ingredients/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_restaurant_ingredient_category_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_ingredient_category_delete')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::deleteCategoryAction',));
                }
                not_restaurant_ingredient_category_delete:

                // restaurant_ingredient_new
                if (preg_match('#^/restaurant/ingredients/(?P<id>[^/]++)/new/ingredient$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_restaurant_ingredient_new;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_ingredient_new')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::newIngredientAction',));
                }
                not_restaurant_ingredient_new:

                // restaurant_ingredient_create
                if (preg_match('#^/restaurant/ingredients/(?P<id>[^/]++)/create/ingredient$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_restaurant_ingredient_create;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_ingredient_create')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::createIngredientAction',));
                }
                not_restaurant_ingredient_create:

                // restaurant_ingredient_edit
                if (preg_match('#^/restaurant/ingredients/(?P<id>[^/]++)/edit/ingredient$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_restaurant_ingredient_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_ingredient_edit')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::editIngredientAction',));
                }
                not_restaurant_ingredient_edit:

                // restaurant_ingredient_update
                if (preg_match('#^/restaurant/ingredients/(?P<id>[^/]++)/ingredient/update$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_restaurant_ingredient_update;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_ingredient_update')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::updateIngredientAction',));
                }
                not_restaurant_ingredient_update:

                // restaurant_ingredient_delete
                if (preg_match('#^/restaurant/ingredients/(?P<id>[^/]++)/ingredient/delete$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_restaurant_ingredient_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_ingredient_delete')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\IngredientController::deleteIngredientAction',));
                }
                not_restaurant_ingredient_delete:

            }

            if (0 === strpos($pathinfo, '/restaurant/tables')) {
                // restaurant_table_list
                if (rtrim($pathinfo, '/') === '/restaurant/tables') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', 'restaurant_table_list');
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\TableController::indexAction',  '_route' => 'restaurant_table_list',);
                }

                // restaurant_table_new
                if ($pathinfo === '/restaurant/tables/new') {
                    return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\TableController::newTableAction',  '_route' => 'restaurant_table_new',);
                }

                // restaurant_table_edit
                if (preg_match('#^/restaurant/tables/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'POST', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'POST', 'HEAD'));
                        goto not_restaurant_table_edit;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_table_edit')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\TableController::editTableAction',));
                }
                not_restaurant_table_edit:

                // restaurant_table_delete
                if (preg_match('#^/restaurant/tables/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'restaurant_table_delete')), array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\TableController::deleteTableAction',));
                }

                // restaurant_table_quicksave
                if ($pathinfo === '/restaurant/tables/quicksave') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_restaurant_table_quicksave;
                    }

                    return array (  '_controller' => 'MBH\\Bundle\\RestaurantBundle\\Controller\\TableController::quickSaveAction',  '_route' => 'restaurant_table_quicksave',);
                }
                not_restaurant_table_quicksave:

            }

        }

        // _twig_error_test
        if (0 === strpos($pathinfo, '/_error') && preg_match('#^/_error/(?P<code>\\d+)(?:\\.(?P<_format>[^/]++))?$#s', $pathinfo, $matches)) {
            return $this->mergeDefaults(array_replace($matches, array('_route' => '_twig_error_test')), array (  '_controller' => 'twig.controller.preview_error:previewErrorPageAction',  '_format' => 'html',));
        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
