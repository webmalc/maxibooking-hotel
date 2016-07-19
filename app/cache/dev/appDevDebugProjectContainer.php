<?php

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * appDevDebugProjectContainer.
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 */
class appDevDebugProjectContainer extends Container
{
    private $parameters;
    private $targetDirs = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $dir = __DIR__;
        for ($i = 1; $i <= 5; ++$i) {
            $this->targetDirs[$i] = $dir = dirname($dir);
        }
        $this->parameters = $this->getDefaultParameters();

        $this->services =
        $this->scopedServices =
        $this->scopeStacks = array();
        $this->scopes = array('request' => 'container');
        $this->scopeChildren = array('request' => array());
        $this->methodMap = array(
            'annotation_reader' => 'getAnnotationReaderService',
            'assetic.asset_factory' => 'getAssetic_AssetFactoryService',
            'assetic.asset_manager' => 'getAssetic_AssetManagerService',
            'assetic.filter.cssrewrite' => 'getAssetic_Filter_CssrewriteService',
            'assetic.filter.less' => 'getAssetic_Filter_LessService',
            'assetic.filter.scssphp' => 'getAssetic_Filter_ScssphpService',
            'assetic.filter.uglifycss' => 'getAssetic_Filter_UglifycssService',
            'assetic.filter.uglifyjs2' => 'getAssetic_Filter_Uglifyjs2Service',
            'assetic.filter_manager' => 'getAssetic_FilterManagerService',
            'assets.context' => 'getAssets_ContextService',
            'assets.packages' => 'getAssets_PackagesService',
            'cache_clearer' => 'getCacheClearerService',
            'cache_warmer' => 'getCacheWarmerService',
            'config_cache_factory' => 'getConfigCacheFactoryService',
            'controller_name_converter' => 'getControllerNameConverterService',
            'data_collector.dump' => 'getDataCollector_DumpService',
            'data_collector.form' => 'getDataCollector_FormService',
            'data_collector.form.extractor' => 'getDataCollector_Form_ExtractorService',
            'data_collector.request' => 'getDataCollector_RequestService',
            'data_collector.router' => 'getDataCollector_RouterService',
            'data_collector.translation' => 'getDataCollector_TranslationService',
            'debug.controller_resolver' => 'getDebug_ControllerResolverService',
            'debug.debug_handlers_listener' => 'getDebug_DebugHandlersListenerService',
            'debug.dump_listener' => 'getDebug_DumpListenerService',
            'debug.event_dispatcher' => 'getDebug_EventDispatcherService',
            'debug.stopwatch' => 'getDebug_StopwatchService',
            'doctrine_mongo_db_param_converter' => 'getDoctrineMongoDbParamConverterService',
            'doctrine_mongodb' => 'getDoctrineMongodbService',
            'doctrine_mongodb.odm.cache.array' => 'getDoctrineMongodb_Odm_Cache_ArrayService',
            'doctrine_mongodb.odm.data_collector.pretty' => 'getDoctrineMongodb_Odm_DataCollector_PrettyService',
            'doctrine_mongodb.odm.default_configuration' => 'getDoctrineMongodb_Odm_DefaultConfigurationService',
            'doctrine_mongodb.odm.default_connection' => 'getDoctrineMongodb_Odm_DefaultConnectionService',
            'doctrine_mongodb.odm.default_document_manager' => 'getDoctrineMongodb_Odm_DefaultDocumentManagerService',
            'doctrine_mongodb.odm.default_manager_configurator' => 'getDoctrineMongodb_Odm_DefaultManagerConfiguratorService',
            'doctrine_mongodb.odm.event_manager' => 'getDoctrineMongodb_Odm_EventManagerService',
            'doctrine_mongodb.odm.metadata.annotation' => 'getDoctrineMongodb_Odm_Metadata_AnnotationService',
            'doctrine_mongodb.odm.metadata.chain' => 'getDoctrineMongodb_Odm_Metadata_ChainService',
            'doctrine_mongodb.odm.metadata.xml' => 'getDoctrineMongodb_Odm_Metadata_XmlService',
            'doctrine_mongodb.odm.metadata.yml' => 'getDoctrineMongodb_Odm_Metadata_YmlService',
            'doctrine_odm.mongodb.validator.unique' => 'getDoctrineOdm_Mongodb_Validator_UniqueService',
            'doctrine_odm.mongodb.validator_initializer' => 'getDoctrineOdm_Mongodb_ValidatorInitializerService',
            'file_locator' => 'getFileLocatorService',
            'filesystem' => 'getFilesystemService',
            'form.csrf_provider' => 'getForm_CsrfProviderService',
            'form.factory' => 'getForm_FactoryService',
            'form.registry' => 'getForm_RegistryService',
            'form.resolved_type_factory' => 'getForm_ResolvedTypeFactoryService',
            'form.type.birthday' => 'getForm_Type_BirthdayService',
            'form.type.button' => 'getForm_Type_ButtonService',
            'form.type.checkbox' => 'getForm_Type_CheckboxService',
            'form.type.choice' => 'getForm_Type_ChoiceService',
            'form.type.collection' => 'getForm_Type_CollectionService',
            'form.type.country' => 'getForm_Type_CountryService',
            'form.type.currency' => 'getForm_Type_CurrencyService',
            'form.type.date' => 'getForm_Type_DateService',
            'form.type.datetime' => 'getForm_Type_DatetimeService',
            'form.type.email' => 'getForm_Type_EmailService',
            'form.type.facilities' => 'getForm_Type_FacilitiesService',
            'form.type.file' => 'getForm_Type_FileService',
            'form.type.form' => 'getForm_Type_FormService',
            'form.type.hidden' => 'getForm_Type_HiddenService',
            'form.type.integer' => 'getForm_Type_IntegerService',
            'form.type.language' => 'getForm_Type_LanguageService',
            'form.type.locale' => 'getForm_Type_LocaleService',
            'form.type.money' => 'getForm_Type_MoneyService',
            'form.type.mongodb_document' => 'getForm_Type_MongodbDocumentService',
            'form.type.number' => 'getForm_Type_NumberService',
            'form.type.password' => 'getForm_Type_PasswordService',
            'form.type.percent' => 'getForm_Type_PercentService',
            'form.type.radio' => 'getForm_Type_RadioService',
            'form.type.range' => 'getForm_Type_RangeService',
            'form.type.repeated' => 'getForm_Type_RepeatedService',
            'form.type.reset' => 'getForm_Type_ResetService',
            'form.type.search' => 'getForm_Type_SearchService',
            'form.type.submit' => 'getForm_Type_SubmitService',
            'form.type.text' => 'getForm_Type_TextService',
            'form.type.textarea' => 'getForm_Type_TextareaService',
            'form.type.time' => 'getForm_Type_TimeService',
            'form.type.timezone' => 'getForm_Type_TimezoneService',
            'form.type.url' => 'getForm_Type_UrlService',
            'form.type_extension.csrf' => 'getForm_TypeExtension_CsrfService',
            'form.type_extension.form.data_collector' => 'getForm_TypeExtension_Form_DataCollectorService',
            'form.type_extension.form.http_foundation' => 'getForm_TypeExtension_Form_HttpFoundationService',
            'form.type_extension.form.validator' => 'getForm_TypeExtension_Form_ValidatorService',
            'form.type_extension.repeated.validator' => 'getForm_TypeExtension_Repeated_ValidatorService',
            'form.type_extension.submit.validator' => 'getForm_TypeExtension_Submit_ValidatorService',
            'form.type_guesser.doctrine.mongodb' => 'getForm_TypeGuesser_Doctrine_MongodbService',
            'form.type_guesser.validator' => 'getForm_TypeGuesser_ValidatorService',
            'fos_js_routing.controller' => 'getFosJsRouting_ControllerService',
            'fos_js_routing.extractor' => 'getFosJsRouting_ExtractorService',
            'fos_js_routing.serializer' => 'getFosJsRouting_SerializerService',
            'fos_user.change_password.form.factory' => 'getFosUser_ChangePassword_Form_FactoryService',
            'fos_user.change_password.form.type' => 'getFosUser_ChangePassword_Form_TypeService',
            'fos_user.document_manager' => 'getFosUser_DocumentManagerService',
            'fos_user.group.form.factory' => 'getFosUser_Group_Form_FactoryService',
            'fos_user.group.form.type' => 'getFosUser_Group_Form_TypeService',
            'fos_user.group_manager' => 'getFosUser_GroupManagerService',
            'fos_user.listener.authentication' => 'getFosUser_Listener_AuthenticationService',
            'fos_user.listener.flash' => 'getFosUser_Listener_FlashService',
            'fos_user.listener.resetting' => 'getFosUser_Listener_ResettingService',
            'fos_user.mailer' => 'getFosUser_MailerService',
            'fos_user.profile.form.factory' => 'getFosUser_Profile_Form_FactoryService',
            'fos_user.profile.form.type' => 'getFosUser_Profile_Form_TypeService',
            'fos_user.registration.form.factory' => 'getFosUser_Registration_Form_FactoryService',
            'fos_user.registration.form.type' => 'getFosUser_Registration_Form_TypeService',
            'fos_user.resetting.form.factory' => 'getFosUser_Resetting_Form_FactoryService',
            'fos_user.resetting.form.type' => 'getFosUser_Resetting_Form_TypeService',
            'fos_user.security.interactive_login_listener' => 'getFosUser_Security_InteractiveLoginListenerService',
            'fos_user.security.login_manager' => 'getFosUser_Security_LoginManagerService',
            'fos_user.user_manager' => 'getFosUser_UserManagerService',
            'fos_user.user_provider.username_email' => 'getFosUser_UserProvider_UsernameEmailService',
            'fos_user.username_form_type' => 'getFosUser_UsernameFormTypeService',
            'fos_user.util.email_canonicalizer' => 'getFosUser_Util_EmailCanonicalizerService',
            'fos_user.util.token_generator' => 'getFosUser_Util_TokenGeneratorService',
            'fos_user.util.user_manipulator' => 'getFosUser_Util_UserManipulatorService',
            'fragment.handler' => 'getFragment_HandlerService',
            'fragment.listener' => 'getFragment_ListenerService',
            'fragment.renderer.esi' => 'getFragment_Renderer_EsiService',
            'fragment.renderer.hinclude' => 'getFragment_Renderer_HincludeService',
            'fragment.renderer.inline' => 'getFragment_Renderer_InlineService',
            'fragment.renderer.ssi' => 'getFragment_Renderer_SsiService',
            'gedmo.listener.translatable' => 'getGedmo_Listener_TranslatableService',
            'gravatar.api' => 'getGravatar_ApiService',
            'guzzle.client' => 'getGuzzle_ClientService',
            'guzzle.service_builder' => 'getGuzzle_ServiceBuilderService',
            'http_kernel' => 'getHttpKernelService',
            'jms_aop.interceptor_loader' => 'getJmsAop_InterceptorLoaderService',
            'jms_aop.pointcut_container' => 'getJmsAop_PointcutContainerService',
            'jms_di_extra.controller_resolver' => 'getJmsDiExtra_ControllerResolverService',
            'jms_di_extra.metadata.converter' => 'getJmsDiExtra_Metadata_ConverterService',
            'jms_di_extra.metadata.metadata_factory' => 'getJmsDiExtra_Metadata_MetadataFactoryService',
            'jms_di_extra.metadata_driver' => 'getJmsDiExtra_MetadataDriverService',
            'jms_translation.config_factory' => 'getJmsTranslation_ConfigFactoryService',
            'jms_translation.loader_manager' => 'getJmsTranslation_LoaderManagerService',
            'jms_translation.twig_extension' => 'getJmsTranslation_TwigExtensionService',
            'jms_translation.updater' => 'getJmsTranslation_UpdaterService',
            'kernel' => 'getKernelService',
            'kernel.class_cache.cache_warmer' => 'getKernel_ClassCache_CacheWarmerService',
            'knp_menu.factory' => 'getKnpMenu_FactoryService',
            'knp_menu.listener.voters' => 'getKnpMenu_Listener_VotersService',
            'knp_menu.matcher' => 'getKnpMenu_MatcherService',
            'knp_menu.menu_provider' => 'getKnpMenu_MenuProviderService',
            'knp_menu.renderer.list' => 'getKnpMenu_Renderer_ListService',
            'knp_menu.renderer.twig' => 'getKnpMenu_Renderer_TwigService',
            'knp_menu.renderer_provider' => 'getKnpMenu_RendererProviderService',
            'knp_menu.voter.router' => 'getKnpMenu_Voter_RouterService',
            'knp_snappy.image' => 'getKnpSnappy_ImageService',
            'knp_snappy.pdf' => 'getKnpSnappy_PdfService',
            'liip_imagine' => 'getLiipImagineService',
            'liip_imagine.binary.loader.default' => 'getLiipImagine_Binary_Loader_DefaultService',
            'liip_imagine.binary.loader.protected' => 'getLiipImagine_Binary_Loader_ProtectedService',
            'liip_imagine.binary.loader.prototype.filesystem' => 'getLiipImagine_Binary_Loader_Prototype_FilesystemService',
            'liip_imagine.binary.loader.prototype.stream' => 'getLiipImagine_Binary_Loader_Prototype_StreamService',
            'liip_imagine.binary.mime_type_guesser' => 'getLiipImagine_Binary_MimeTypeGuesserService',
            'liip_imagine.cache.manager' => 'getLiipImagine_Cache_ManagerService',
            'liip_imagine.cache.resolver.default' => 'getLiipImagine_Cache_Resolver_DefaultService',
            'liip_imagine.cache.resolver.no_cache_web_path' => 'getLiipImagine_Cache_Resolver_NoCacheWebPathService',
            'liip_imagine.cache.signer' => 'getLiipImagine_Cache_SignerService',
            'liip_imagine.controller' => 'getLiipImagine_ControllerService',
            'liip_imagine.data.manager' => 'getLiipImagine_Data_ManagerService',
            'liip_imagine.extension_guesser' => 'getLiipImagine_ExtensionGuesserService',
            'liip_imagine.filter.configuration' => 'getLiipImagine_Filter_ConfigurationService',
            'liip_imagine.filter.loader.auto_rotate' => 'getLiipImagine_Filter_Loader_AutoRotateService',
            'liip_imagine.filter.loader.background' => 'getLiipImagine_Filter_Loader_BackgroundService',
            'liip_imagine.filter.loader.crop' => 'getLiipImagine_Filter_Loader_CropService',
            'liip_imagine.filter.loader.interlace' => 'getLiipImagine_Filter_Loader_InterlaceService',
            'liip_imagine.filter.loader.paste' => 'getLiipImagine_Filter_Loader_PasteService',
            'liip_imagine.filter.loader.relative_resize' => 'getLiipImagine_Filter_Loader_RelativeResizeService',
            'liip_imagine.filter.loader.resize' => 'getLiipImagine_Filter_Loader_ResizeService',
            'liip_imagine.filter.loader.rotate' => 'getLiipImagine_Filter_Loader_RotateService',
            'liip_imagine.filter.loader.strip' => 'getLiipImagine_Filter_Loader_StripService',
            'liip_imagine.filter.loader.thumbnail' => 'getLiipImagine_Filter_Loader_ThumbnailService',
            'liip_imagine.filter.loader.upscale' => 'getLiipImagine_Filter_Loader_UpscaleService',
            'liip_imagine.filter.loader.watermark' => 'getLiipImagine_Filter_Loader_WatermarkService',
            'liip_imagine.filter.manager' => 'getLiipImagine_Filter_ManagerService',
            'liip_imagine.filter.post_processor.jpegoptim' => 'getLiipImagine_Filter_PostProcessor_JpegoptimService',
            'liip_imagine.form.type.image' => 'getLiipImagine_Form_Type_ImageService',
            'liip_imagine.mime_type_guesser' => 'getLiipImagine_MimeTypeGuesserService',
            'liip_imagine.templating.helper' => 'getLiipImagine_Templating_HelperService',
            'locale_listener' => 'getLocaleListenerService',
            'logger' => 'getLoggerService',
            'mbh.base_on_controller_listener' => 'getMbh_BaseOnControllerListenerService',
            'mbh.base_on_request_listener' => 'getMbh_BaseOnRequestListenerService',
            'mbh.cache' => 'getMbh_CacheService',
            'mbh.calculation' => 'getMbh_CalculationService',
            'mbh.cash' => 'getMbh_CashService',
            'mbh.cash.1c_exporter' => 'getMbh_Cash_1cExporterService',
            'mbh.cash.document.subscriber' => 'getMbh_Cash_Document_SubscriberService',
            'mbh.channelmanager' => 'getMbh_ChannelmanagerService',
            'mbh.channelmanager.booking' => 'getMbh_Channelmanager_BookingService',
            'mbh.channelmanager.booking_type' => 'getMbh_Channelmanager_BookingTypeService',
            'mbh.channelmanager.configs.subscriber' => 'getMbh_Channelmanager_Configs_SubscriberService',
            'mbh.channelmanager.logger' => 'getMbh_Channelmanager_LoggerService',
            'mbh.channelmanager.logger_handler' => 'getMbh_Channelmanager_LoggerHandlerService',
            'mbh.channelmanager.myallocator' => 'getMbh_Channelmanager_MyallocatorService',
            'mbh.channelmanager.myallocator_type' => 'getMbh_Channelmanager_MyallocatorTypeService',
            'mbh.channelmanager.oktogo' => 'getMbh_Channelmanager_OktogoService',
            'mbh.channelmanager.ostrovok' => 'getMbh_Channelmanager_OstrovokService',
            'mbh.channelmanager.vashotel' => 'getMbh_Channelmanager_VashotelService',
            'mbh.check_hotel.action_listener' => 'getMbh_CheckHotel_ActionListenerService',
            'mbh.currency' => 'getMbh_CurrencyService',
            'mbh.event_listener.check_relation_subscriber' => 'getMbh_EventListener_CheckRelationSubscriberService',
            'mbh.event_listener.generate_internationl_listener' => 'getMbh_EventListener_GenerateInternationlListenerService',
            'mbh.event_listener.hotelable_listener' => 'getMbh_EventListener_HotelableListenerService',
            'mbh.event_listener.versioned_subscriber' => 'getMbh_EventListener_VersionedSubscriberService',
            'mbh.facility_repository' => 'getMbh_FacilityRepositoryService',
            'mbh.form.bottom_extension' => 'getMbh_Form_BottomExtensionService',
            'mbh.form.fieldset_extension' => 'getMbh_Form_FieldsetExtensionService',
            'mbh.form.help_extension' => 'getMbh_Form_HelpExtensionService',
            'mbh.get_set_method_normalizer' => 'getMbh_GetSetMethodNormalizerService',
            'mbh.helper' => 'getMbh_HelperService',
            'mbh.hotel.auto_task_creator' => 'getMbh_Hotel_AutoTaskCreatorService',
            'mbh.hotel.console_auto_task_creator' => 'getMbh_Hotel_ConsoleAutoTaskCreatorService',
            'mbh.hotel.facility.subscriber' => 'getMbh_Hotel_Facility_SubscriberService',
            'mbh.hotel.hotel_manager' => 'getMbh_Hotel_HotelManagerService',
            'mbh.hotel.room.subscriber' => 'getMbh_Hotel_Room_SubscriberService',
            'mbh.hotel.room_type.subscriber' => 'getMbh_Hotel_RoomType_SubscriberService',
            'mbh.hotel.room_type_manager' => 'getMbh_Hotel_RoomTypeManagerService',
            'mbh.hotel.selector' => 'getMbh_Hotel_SelectorService',
            'mbh.hotel.task.subscriber' => 'getMbh_Hotel_Task_SubscriberService',
            'mbh.hotel.task_repository' => 'getMbh_Hotel_TaskRepositoryService',
            'mbh.mailer' => 'getMbh_MailerService',
            'mbh.mbhs' => 'getMbh_MbhsService',
            'mbh.mongo' => 'getMbh_MongoService',
            'mbh.notifier' => 'getMbh_NotifierService',
            'mbh.notifier.mailer' => 'getMbh_Notifier_MailerService',
            'mbh.online.logger' => 'getMbh_Online_LoggerService',
            'mbh.online.logger_handler' => 'getMbh_Online_LoggerHandlerService',
            'mbh.order_manager' => 'getMbh_OrderManagerService',
            'mbh.package.document_factory' => 'getMbh_Package_DocumentFactoryService',
            'mbh.package.document_tempalte_factory' => 'getMbh_Package_DocumentTempalteFactoryService',
            'mbh.package.document_xls_factory' => 'getMbh_Package_DocumentXlsFactoryService',
            'mbh.package.form.type.address_object_decomposed' => 'getMbh_Package_Form_Type_AddressObjectDecomposedService',
            'mbh.package.form.type.birthplace' => 'getMbh_Package_Form_Type_BirthplaceService',
            'mbh.package.form.type.document_relation' => 'getMbh_Package_Form_Type_DocumentRelationService',
            'mbh.package.order.subscriber' => 'getMbh_Package_Order_SubscriberService',
            'mbh.package.payer_repository' => 'getMbh_Package_PayerRepositoryService',
            'mbh.package.permissions' => 'getMbh_Package_PermissionsService',
            'mbh.package.report.filling_report_generator' => 'getMbh_Package_Report_FillingReportGeneratorService',
            'mbh.package.search' => 'getMbh_Package_SearchService',
            'mbh.package.search_multiple_dates' => 'getMbh_Package_SearchMultipleDatesService',
            'mbh.package.search_simple' => 'getMbh_Package_SearchSimpleService',
            'mbh.package.search_with_tariffs' => 'getMbh_Package_SearchWithTariffsService',
            'mbh.package.subscriber' => 'getMbh_Package_SubscriberService',
            'mbh.package.subscriber.tourist' => 'getMbh_Package_Subscriber_TouristService',
            'mbh.package.unwelcome_repository' => 'getMbh_Package_UnwelcomeRepositoryService',
            'mbh.package.validator' => 'getMbh_Package_ValidatorService',
            'mbh.pdf_generator' => 'getMbh_PdfGeneratorService',
            'mbh.price.cache' => 'getMbh_Price_CacheService',
            'mbh.restriction' => 'getMbh_RestrictionService',
            'mbh.room.cache' => 'getMbh_Room_CacheService',
            'mbh.room.cache.graph.generator' => 'getMbh_Room_Cache_Graph_GeneratorService',
            'mbh.room_cache.subscriber' => 'getMbh_RoomCache_SubscriberService',
            'mbh.system.messenger' => 'getMbh_System_MessengerService',
            'mbh.tariff.subscriber' => 'getMbh_Tariff_SubscriberService',
            'mbh.testaurant.subscriber' => 'getMbh_Testaurant_SubscriberService',
            'mbh.tourists.messenger' => 'getMbh_Tourists_MessengerService',
            'mbh.twig.extension' => 'getMbh_Twig_ExtensionService',
            'mbh.twig.hotel_selector_extension' => 'getMbh_Twig_HotelSelectorExtensionService',
            'mbh.user.group.type' => 'getMbh_User_Group_TypeService',
            'mbh.user.metadata_listener' => 'getMbh_User_MetadataListenerService',
            'mbh.user.roles.type' => 'getMbh_User_Roles_TypeService',
            'mbh.user.validator' => 'getMbh_User_ValidatorService',
            'mbh.user.work_shift_listener' => 'getMbh_User_WorkShiftListenerService',
            'mbh.user.work_shift_manager' => 'getMbh_User_WorkShiftManagerService',
            'mbh.user.work_shift_repository' => 'getMbh_User_WorkShiftRepositoryService',
            'mbh.validator.range' => 'getMbh_Validator_RangeService',
            'mbh.vega.dictionary_provider' => 'getMbh_Vega_DictionaryProviderService',
            'mbh.vega.vega_export' => 'getMbh_Vega_VegaExportService',
            'mbh.warehouse.subscriber' => 'getMbh_Warehouse_SubscriberService',
            'mbh__restaurant.form.dish_menu_ingredient_embedded_type' => 'getMbhRestaurant_Form_DishMenuIngredientEmbeddedTypeService',
            'mbh__restaurant.form_dish_order.dish_order_item_emmbedded_type' => 'getMbhRestaurant_FormDishOrder_DishOrderItemEmmbeddedTypeService',
            'mbh__restaurant.form_dish_order.dish_order_item_type' => 'getMbhRestaurant_FormDishOrder_DishOrderItemTypeService',
            'memcache.data_collector' => 'getMemcache_DataCollectorService',
            'memcache.default' => 'getMemcache_DefaultService',
            'memcache.session_handler' => 'getMemcache_SessionHandlerService',
            'misd_guzzle.cache.doctrine.filesystem' => 'getMisdGuzzle_Cache_Doctrine_FilesystemService',
            'misd_guzzle.cache.doctrine.filesystem.adapter' => 'getMisdGuzzle_Cache_Doctrine_Filesystem_AdapterService',
            'misd_guzzle.cache.filesystem' => 'getMisdGuzzle_Cache_FilesystemService',
            'misd_guzzle.listener.command_listener' => 'getMisdGuzzle_Listener_CommandListenerService',
            'misd_guzzle.listener.request_listener' => 'getMisdGuzzle_Listener_RequestListenerService',
            'misd_guzzle.log.adapter.array' => 'getMisdGuzzle_Log_Adapter_ArrayService',
            'misd_guzzle.log.array' => 'getMisdGuzzle_Log_ArrayService',
            'misd_guzzle.log.monolog' => 'getMisdGuzzle_Log_MonologService',
            'misd_guzzle.param_converter' => 'getMisdGuzzle_ParamConverterService',
            'misd_guzzle.request.visitor.body' => 'getMisdGuzzle_Request_Visitor_BodyService',
            'misd_guzzle.response.parser' => 'getMisdGuzzle_Response_ParserService',
            'misd_guzzle.response.parser.fallback' => 'getMisdGuzzle_Response_Parser_FallbackService',
            'monolog.handler.console' => 'getMonolog_Handler_ConsoleService',
            'monolog.handler.debug' => 'getMonolog_Handler_DebugService',
            'monolog.handler.main' => 'getMonolog_Handler_MainService',
            'monolog.logger.assetic' => 'getMonolog_Logger_AsseticService',
            'monolog.logger.doctrine' => 'getMonolog_Logger_DoctrineService',
            'monolog.logger.event' => 'getMonolog_Logger_EventService',
            'monolog.logger.php' => 'getMonolog_Logger_PhpService',
            'monolog.logger.profiler' => 'getMonolog_Logger_ProfilerService',
            'monolog.logger.request' => 'getMonolog_Logger_RequestService',
            'monolog.logger.router' => 'getMonolog_Logger_RouterService',
            'monolog.logger.security' => 'getMonolog_Logger_SecurityService',
            'monolog.logger.snappy' => 'getMonolog_Logger_SnappyService',
            'monolog.logger.templating' => 'getMonolog_Logger_TemplatingService',
            'monolog.logger.translation' => 'getMonolog_Logger_TranslationService',
            'ob_highcharts.twig.highcharts_extension' => 'getObHighcharts_Twig_HighchartsExtensionService',
            'phpexcel' => 'getPhpexcelService',
            'profiler' => 'getProfilerService',
            'profiler_listener' => 'getProfilerListenerService',
            'property_accessor' => 'getPropertyAccessorService',
            'request' => 'getRequestService',
            'request_stack' => 'getRequestStackService',
            'response_listener' => 'getResponseListenerService',
            'router' => 'getRouterService',
            'router.request_context' => 'getRouter_RequestContextService',
            'router_listener' => 'getRouterListenerService',
            'routing.loader' => 'getRouting_LoaderService',
            'security.access.decision_manager' => 'getSecurity_Access_DecisionManagerService',
            'security.acl.provider' => 'getSecurity_Acl_ProviderService',
            'security.authentication.guard_handler' => 'getSecurity_Authentication_GuardHandlerService',
            'security.authentication.manager' => 'getSecurity_Authentication_ManagerService',
            'security.authentication.session_strategy' => 'getSecurity_Authentication_SessionStrategyService',
            'security.authentication.success_handler' => 'getSecurity_Authentication_SuccessHandlerService',
            'security.authentication.success_handler.main.form_login' => 'getSecurity_Authentication_SuccessHandler_Main_FormLoginService',
            'security.authentication.trust_resolver' => 'getSecurity_Authentication_TrustResolverService',
            'security.authentication_utils' => 'getSecurity_AuthenticationUtilsService',
            'security.authorization_checker' => 'getSecurity_AuthorizationCheckerService',
            'security.context' => 'getSecurity_ContextService',
            'security.csrf.token_manager' => 'getSecurity_Csrf_TokenManagerService',
            'security.encoder_factory' => 'getSecurity_EncoderFactoryService',
            'security.firewall' => 'getSecurity_FirewallService',
            'security.firewall.map.context.dev' => 'getSecurity_Firewall_Map_Context_DevService',
            'security.firewall.map.context.main' => 'getSecurity_Firewall_Map_Context_MainService',
            'security.http_utils' => 'getSecurity_HttpUtilsService',
            'security.logout_url_generator' => 'getSecurity_LogoutUrlGeneratorService',
            'security.password_encoder' => 'getSecurity_PasswordEncoderService',
            'security.rememberme.response_listener' => 'getSecurity_Rememberme_ResponseListenerService',
            'security.role_hierarchy' => 'getSecurity_RoleHierarchyService',
            'security.secure_random' => 'getSecurity_SecureRandomService',
            'security.token_storage' => 'getSecurity_TokenStorageService',
            'security.user_checker.main' => 'getSecurity_UserChecker_MainService',
            'security.validator.user_password' => 'getSecurity_Validator_UserPasswordService',
            'sensio_distribution.security_checker' => 'getSensioDistribution_SecurityCheckerService',
            'sensio_distribution.security_checker.command' => 'getSensioDistribution_SecurityChecker_CommandService',
            'sensio_distribution.webconfigurator' => 'getSensioDistribution_WebconfiguratorService',
            'sensio_framework_extra.cache.listener' => 'getSensioFrameworkExtra_Cache_ListenerService',
            'sensio_framework_extra.controller.listener' => 'getSensioFrameworkExtra_Controller_ListenerService',
            'sensio_framework_extra.converter.datetime' => 'getSensioFrameworkExtra_Converter_DatetimeService',
            'sensio_framework_extra.converter.doctrine.orm' => 'getSensioFrameworkExtra_Converter_Doctrine_OrmService',
            'sensio_framework_extra.converter.listener' => 'getSensioFrameworkExtra_Converter_ListenerService',
            'sensio_framework_extra.converter.manager' => 'getSensioFrameworkExtra_Converter_ManagerService',
            'sensio_framework_extra.security.listener' => 'getSensioFrameworkExtra_Security_ListenerService',
            'sensio_framework_extra.view.guesser' => 'getSensioFrameworkExtra_View_GuesserService',
            'sensio_framework_extra.view.listener' => 'getSensioFrameworkExtra_View_ListenerService',
            'serializer' => 'getSerializerService',
            'service_container' => 'getServiceContainerService',
            'session' => 'getSessionService',
            'session.save_listener' => 'getSession_SaveListenerService',
            'session.storage.filesystem' => 'getSession_Storage_FilesystemService',
            'session.storage.metadata_bag' => 'getSession_Storage_MetadataBagService',
            'session.storage.native' => 'getSession_Storage_NativeService',
            'session.storage.php_bridge' => 'getSession_Storage_PhpBridgeService',
            'session_listener' => 'getSessionListenerService',
            'stof_doctrine_extensions.event_listener.blame' => 'getStofDoctrineExtensions_EventListener_BlameService',
            'stof_doctrine_extensions.event_listener.locale' => 'getStofDoctrineExtensions_EventListener_LocaleService',
            'stof_doctrine_extensions.event_listener.logger' => 'getStofDoctrineExtensions_EventListener_LoggerService',
            'stof_doctrine_extensions.listener.blameable' => 'getStofDoctrineExtensions_Listener_BlameableService',
            'stof_doctrine_extensions.listener.loggable' => 'getStofDoctrineExtensions_Listener_LoggableService',
            'stof_doctrine_extensions.listener.translatable' => 'getStofDoctrineExtensions_Listener_TranslatableService',
            'stof_doctrine_extensions.uploadable.manager' => 'getStofDoctrineExtensions_Uploadable_ManagerService',
            'streamed_response_listener' => 'getStreamedResponseListenerService',
            'swiftmailer.email_sender.listener' => 'getSwiftmailer_EmailSender_ListenerService',
            'swiftmailer.mailer.default' => 'getSwiftmailer_Mailer_DefaultService',
            'swiftmailer.mailer.default.plugin.messagelogger' => 'getSwiftmailer_Mailer_Default_Plugin_MessageloggerService',
            'swiftmailer.mailer.default.spool' => 'getSwiftmailer_Mailer_Default_SpoolService',
            'swiftmailer.mailer.default.transport' => 'getSwiftmailer_Mailer_Default_TransportService',
            'swiftmailer.mailer.default.transport.eventdispatcher' => 'getSwiftmailer_Mailer_Default_Transport_EventdispatcherService',
            'swiftmailer.mailer.default.transport.real' => 'getSwiftmailer_Mailer_Default_Transport_RealService',
            'templating' => 'getTemplatingService',
            'templating.filename_parser' => 'getTemplating_FilenameParserService',
            'templating.helper.assets' => 'getTemplating_Helper_AssetsService',
            'templating.helper.gravatar' => 'getTemplating_Helper_GravatarService',
            'templating.helper.logout_url' => 'getTemplating_Helper_LogoutUrlService',
            'templating.helper.router' => 'getTemplating_Helper_RouterService',
            'templating.helper.security' => 'getTemplating_Helper_SecurityService',
            'templating.loader' => 'getTemplating_LoaderService',
            'templating.locator' => 'getTemplating_LocatorService',
            'templating.name_parser' => 'getTemplating_NameParserService',
            'translation.dumper.csv' => 'getTranslation_Dumper_CsvService',
            'translation.dumper.ini' => 'getTranslation_Dumper_IniService',
            'translation.dumper.json' => 'getTranslation_Dumper_JsonService',
            'translation.dumper.mo' => 'getTranslation_Dumper_MoService',
            'translation.dumper.php' => 'getTranslation_Dumper_PhpService',
            'translation.dumper.po' => 'getTranslation_Dumper_PoService',
            'translation.dumper.qt' => 'getTranslation_Dumper_QtService',
            'translation.dumper.res' => 'getTranslation_Dumper_ResService',
            'translation.dumper.xliff' => 'getTranslation_Dumper_XliffService',
            'translation.dumper.yml' => 'getTranslation_Dumper_YmlService',
            'translation.extractor' => 'getTranslation_ExtractorService',
            'translation.extractor.php' => 'getTranslation_Extractor_PhpService',
            'translation.loader' => 'getTranslation_LoaderService',
            'translation.loader.csv' => 'getTranslation_Loader_CsvService',
            'translation.loader.dat' => 'getTranslation_Loader_DatService',
            'translation.loader.ini' => 'getTranslation_Loader_IniService',
            'translation.loader.json' => 'getTranslation_Loader_JsonService',
            'translation.loader.mo' => 'getTranslation_Loader_MoService',
            'translation.loader.php' => 'getTranslation_Loader_PhpService',
            'translation.loader.po' => 'getTranslation_Loader_PoService',
            'translation.loader.qt' => 'getTranslation_Loader_QtService',
            'translation.loader.res' => 'getTranslation_Loader_ResService',
            'translation.loader.xliff' => 'getTranslation_Loader_XliffService',
            'translation.loader.yml' => 'getTranslation_Loader_YmlService',
            'translation.writer' => 'getTranslation_WriterService',
            'translator' => 'getTranslatorService',
            'translator.default' => 'getTranslator_DefaultService',
            'translator_listener' => 'getTranslatorListenerService',
            'twig' => 'getTwigService',
            'twig.controller.exception' => 'getTwig_Controller_ExceptionService',
            'twig.controller.preview_error' => 'getTwig_Controller_PreviewErrorService',
            'twig.exception_listener' => 'getTwig_ExceptionListenerService',
            'twig.extension.gravatar' => 'getTwig_Extension_GravatarService',
            'twig.loader' => 'getTwig_LoaderService',
            'twig.profile' => 'getTwig_ProfileService',
            'twig.text_extension' => 'getTwig_TextExtensionService',
            'twig.translation.extractor' => 'getTwig_Translation_ExtractorService',
            'uri_signer' => 'getUriSignerService',
            'validator' => 'getValidatorService',
            'validator.builder' => 'getValidator_BuilderService',
            'validator.email' => 'getValidator_EmailService',
            'validator.expression' => 'getValidator_ExpressionService',
            'var_dumper.cli_dumper' => 'getVarDumper_CliDumperService',
            'var_dumper.cloner' => 'getVarDumper_ClonerService',
            'web_profiler.controller.exception' => 'getWebProfiler_Controller_ExceptionService',
            'web_profiler.controller.profiler' => 'getWebProfiler_Controller_ProfilerService',
            'web_profiler.controller.router' => 'getWebProfiler_Controller_RouterService',
            'web_profiler.debug_toolbar' => 'getWebProfiler_DebugToolbarService',
        );
        $this->aliases = array(
            'console.command.sensiolabs_security_command_securitycheckercommand' => 'sensio_distribution.security_checker.command',
            'doctrine.odm.mongodb.document_manager' => 'doctrine_mongodb.odm.default_document_manager',
            'doctrine_mongodb.odm.cache' => 'doctrine_mongodb.odm.cache.array',
            'doctrine_mongodb.odm.document_manager' => 'doctrine_mongodb.odm.default_document_manager',
            'doctrine_mongodb.odm.metadata.annotation_reader' => 'annotation_reader',
            'event_dispatcher' => 'debug.event_dispatcher',
            'fos_user.util.username_canonicalizer' => 'fos_user.util.email_canonicalizer',
            'mailer' => 'swiftmailer.mailer.default',
            'sensio.distribution.webconfigurator' => 'sensio_distribution.webconfigurator',
            'session.handler' => 'memcache.session_handler',
            'session.storage' => 'session.storage.native',
            'swiftmailer.mailer' => 'swiftmailer.mailer.default',
            'swiftmailer.plugin.messagelogger' => 'swiftmailer.mailer.default.plugin.messagelogger',
            'swiftmailer.spool' => 'swiftmailer.mailer.default.spool',
            'swiftmailer.transport' => 'swiftmailer.mailer.default.transport',
            'swiftmailer.transport.real' => 'swiftmailer.mailer.default.transport.real',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        throw new LogicException('You cannot compile a dumped frozen container.');
    }

    /**
     * Gets the 'annotation_reader' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Common\Annotations\CachedReader A Doctrine\Common\Annotations\CachedReader instance.
     */
    protected function getAnnotationReaderService()
    {
        return $this->services['annotation_reader'] = new \Doctrine\Common\Annotations\CachedReader(new \Doctrine\Common\Annotations\AnnotationReader(), new \Doctrine\Common\Cache\FilesystemCache((__DIR__.'/annotations')), true);
    }

    /**
     * Gets the 'assetic.asset_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Assetic\Factory\LazyAssetManager A Assetic\Factory\LazyAssetManager instance.
     */
    protected function getAssetic_AssetManagerService()
    {
        $a = $this->get('templating.loader');

        $this->services['assetic.asset_manager'] = $instance = new \Assetic\Factory\LazyAssetManager($this->get('assetic.asset_factory'), array('twig' => new \Assetic\Factory\Loader\CachedFormulaLoader(new \Assetic\Extension\Twig\TwigFormulaLoader($this->get('twig'), $this->get('monolog.logger.assetic', ContainerInterface::NULL_ON_INVALID_REFERENCE)), new \Assetic\Cache\ConfigCache((__DIR__.'/assetic/config')), true)));

        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'FrameworkBundle', ($this->targetDirs[2].'/Resources/FrameworkBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'FrameworkBundle', ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'SecurityBundle', ($this->targetDirs[2].'/Resources/SecurityBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'SecurityBundle', ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bundle/SecurityBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'TwigBundle', ($this->targetDirs[2].'/Resources/TwigBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'TwigBundle', ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MonologBundle', ($this->targetDirs[2].'/Resources/MonologBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MonologBundle', ($this->targetDirs[3].'/vendor/symfony/monolog-bundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'SwiftmailerBundle', ($this->targetDirs[2].'/Resources/SwiftmailerBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'SwiftmailerBundle', ($this->targetDirs[3].'/vendor/symfony/swiftmailer-bundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'AsseticBundle', ($this->targetDirs[2].'/Resources/AsseticBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'AsseticBundle', ($this->targetDirs[3].'/vendor/symfony/assetic-bundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'SensioFrameworkExtraBundle', ($this->targetDirs[2].'/Resources/SensioFrameworkExtraBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'SensioFrameworkExtraBundle', ($this->targetDirs[3].'/vendor/sensio/framework-extra-bundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'DoctrineMongoDBBundle', ($this->targetDirs[2].'/Resources/DoctrineMongoDBBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'DoctrineMongoDBBundle', ($this->targetDirs[3].'/vendor/doctrine/mongodb-odm-bundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'StofDoctrineExtensionsBundle', ($this->targetDirs[2].'/Resources/StofDoctrineExtensionsBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'StofDoctrineExtensionsBundle', ($this->targetDirs[3].'/vendor/stof/doctrine-extensions-bundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'FOSUserBundle', ($this->targetDirs[2].'/Resources/FOSUserBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'FOSUserBundle', ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'FOSJsRoutingBundle', ($this->targetDirs[2].'/Resources/FOSJsRoutingBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'FOSJsRoutingBundle', ($this->targetDirs[3].'/vendor/friendsofsymfony/jsrouting-bundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'KnpMenuBundle', ($this->targetDirs[2].'/Resources/KnpMenuBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'KnpMenuBundle', ($this->targetDirs[3].'/vendor/knplabs/knp-menu-bundle/Knp/Bundle/MenuBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'ObHighchartsBundle', ($this->targetDirs[2].'/Resources/ObHighchartsBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'ObHighchartsBundle', ($this->targetDirs[3].'/vendor/ob/highcharts-bundle/Ob/HighchartsBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'KnpSnappyBundle', ($this->targetDirs[2].'/Resources/KnpSnappyBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'KnpSnappyBundle', ($this->targetDirs[3].'/vendor/knplabs/knp-snappy-bundle/Knp/Bundle/SnappyBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MisdGuzzleBundle', ($this->targetDirs[2].'/Resources/MisdGuzzleBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MisdGuzzleBundle', ($this->targetDirs[3].'/vendor/misd/guzzle-bundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'IamPersistentMongoDBAclBundle', ($this->targetDirs[2].'/Resources/IamPersistentMongoDBAclBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'IamPersistentMongoDBAclBundle', ($this->targetDirs[3].'/vendor/iampersistent/mongodb-acl-bundle/IamPersistent/MongoDBAclBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'LiipImagineBundle', ($this->targetDirs[2].'/Resources/LiipImagineBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'LiipImagineBundle', ($this->targetDirs[3].'/vendor/liip/imagine-bundle/Liip/ImagineBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'JMSDiExtraBundle', ($this->targetDirs[2].'/Resources/JMSDiExtraBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'JMSDiExtraBundle', ($this->targetDirs[3].'/vendor/jms/di-extra-bundle/JMS/DiExtraBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'JMSAopBundle', ($this->targetDirs[2].'/Resources/JMSAopBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'JMSAopBundle', ($this->targetDirs[3].'/vendor/jms/aop-bundle/JMS/AopBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'JMSTranslationBundle', ($this->targetDirs[2].'/Resources/JMSTranslationBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'JMSTranslationBundle', ($this->targetDirs[3].'/vendor/jms/translation-bundle/JMS/TranslationBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'LiuggioExcelBundle', ($this->targetDirs[2].'/Resources/LiuggioExcelBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'LiuggioExcelBundle', ($this->targetDirs[3].'/vendor/liuggio/ExcelBundle/Liuggio/ExcelBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OrnicarGravatarBundle', ($this->targetDirs[2].'/Resources/OrnicarGravatarBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'OrnicarGravatarBundle', ($this->targetDirs[3].'/vendor/ornicar/gravatar-bundle/Ornicar/GravatarBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'DoctrineFixturesBundle', ($this->targetDirs[2].'/Resources/DoctrineFixturesBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'DoctrineFixturesBundle', ($this->targetDirs[3].'/vendor/doctrine/doctrine-fixtures-bundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'LswMemcacheBundle', ($this->targetDirs[2].'/Resources/LswMemcacheBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'LswMemcacheBundle', ($this->targetDirs[3].'/vendor/leaseweb/memcache-bundle/Lsw/MemcacheBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHBaseBundle', ($this->targetDirs[2].'/Resources/MBHBaseBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHBaseBundle', ($this->targetDirs[3].'/src/MBH/Bundle/BaseBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHUserBundle', ($this->targetDirs[2].'/Resources/MBHUserBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHUserBundle', ($this->targetDirs[3].'/src/MBH/Bundle/UserBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHHotelBundle', ($this->targetDirs[2].'/Resources/MBHHotelBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHHotelBundle', ($this->targetDirs[3].'/src/MBH/Bundle/HotelBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHPriceBundle', ($this->targetDirs[2].'/Resources/MBHPriceBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHPriceBundle', ($this->targetDirs[3].'/src/MBH/Bundle/PriceBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHPackageBundle', ($this->targetDirs[2].'/Resources/MBHPackageBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHPackageBundle', ($this->targetDirs[3].'/src/MBH/Bundle/PackageBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHCashBundle', ($this->targetDirs[2].'/Resources/MBHCashBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHCashBundle', ($this->targetDirs[3].'/src/MBH/Bundle/CashBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHChannelManagerBundle', ($this->targetDirs[2].'/Resources/MBHChannelManagerBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHChannelManagerBundle', ($this->targetDirs[3].'/src/MBH/Bundle/ChannelManagerBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHOnlineBundle', ($this->targetDirs[2].'/Resources/MBHOnlineBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHOnlineBundle', ($this->targetDirs[3].'/src/MBH/Bundle/OnlineBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHDemoBundle', ($this->targetDirs[2].'/Resources/MBHDemoBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHDemoBundle', ($this->targetDirs[3].'/src/MBH/Bundle/DemoBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHClientBundle', ($this->targetDirs[2].'/Resources/MBHClientBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHClientBundle', ($this->targetDirs[3].'/src/MBH/Bundle/ClientBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHVegaBundle', ($this->targetDirs[2].'/Resources/MBHVegaBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHVegaBundle', ($this->targetDirs[3].'/src/MBH/Bundle/VegaBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHWarehouseBundle', ($this->targetDirs[2].'/Resources/MBHWarehouseBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHWarehouseBundle', ($this->targetDirs[3].'/src/MBH/Bundle/WarehouseBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHRestaurantBundle', ($this->targetDirs[2].'/Resources/MBHRestaurantBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'MBHRestaurantBundle', ($this->targetDirs[3].'/src/MBH/Bundle/RestaurantBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'WebProfilerBundle', ($this->targetDirs[2].'/Resources/WebProfilerBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'WebProfilerBundle', ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'SensioDistributionBundle', ($this->targetDirs[2].'/Resources/SensioDistributionBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'SensioDistributionBundle', ($this->targetDirs[3].'/vendor/sensio/distribution-bundle/Sensio/Bundle/DistributionBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'SensioGeneratorBundle', ($this->targetDirs[2].'/Resources/SensioGeneratorBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'SensioGeneratorBundle', ($this->targetDirs[3].'/vendor/sensio/generator-bundle/Sensio/Bundle/GeneratorBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\CoalescingDirectoryResource(array(0 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'DebugBundle', ($this->targetDirs[2].'/Resources/DebugBundle/views'), '/\\.[^.]+\\.twig$/'), 1 => new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, 'DebugBundle', ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bundle/DebugBundle/Resources/views'), '/\\.[^.]+\\.twig$/'))), 'twig');
        $instance->addResource(new \Symfony\Bundle\AsseticBundle\Factory\Resource\DirectoryResource($a, '', ($this->targetDirs[2].'/Resources/views'), '/\\.[^.]+\\.twig$/'), 'twig');

        return $instance;
    }

    /**
     * Gets the 'assetic.filter.cssrewrite' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Assetic\Filter\CssRewriteFilter A Assetic\Filter\CssRewriteFilter instance.
     */
    protected function getAssetic_Filter_CssrewriteService()
    {
        return $this->services['assetic.filter.cssrewrite'] = new \Assetic\Filter\CssRewriteFilter();
    }

    /**
     * Gets the 'assetic.filter.less' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Assetic\Filter\LessFilter A Assetic\Filter\LessFilter instance.
     */
    protected function getAssetic_Filter_LessService()
    {
        $this->services['assetic.filter.less'] = $instance = new \Assetic\Filter\LessFilter('/usr/bin/nodejs', array(0 => '/usr/local/lib/node_modules/'));

        $instance->setTimeout(NULL);
        $instance->setCompress(NULL);
        $instance->setLoadPaths(array());

        return $instance;
    }

    /**
     * Gets the 'assetic.filter.scssphp' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Assetic\Filter\ScssphpFilter A Assetic\Filter\ScssphpFilter instance.
     */
    protected function getAssetic_Filter_ScssphpService()
    {
        $this->services['assetic.filter.scssphp'] = $instance = new \Assetic\Filter\ScssphpFilter();

        $instance->enableCompass(false);
        $instance->setImportPaths(array());
        $instance->setVariables(array());
        $instance->setFormatter('Leafo\\ScssPhp\\Formatter\\Compressed');

        return $instance;
    }

    /**
     * Gets the 'assetic.filter.uglifycss' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Assetic\Filter\UglifyCssFilter A Assetic\Filter\UglifyCssFilter instance.
     */
    protected function getAssetic_Filter_UglifycssService()
    {
        $this->services['assetic.filter.uglifycss'] = $instance = new \Assetic\Filter\UglifyCssFilter('/usr/local/bin/uglifycss', '/usr/bin/nodejs');

        $instance->setTimeout(NULL);
        $instance->setNodePaths(array());
        $instance->setExpandVars(false);
        $instance->setUglyComments(false);
        $instance->setCuteComments(false);

        return $instance;
    }

    /**
     * Gets the 'assetic.filter.uglifyjs2' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Assetic\Filter\UglifyJs2Filter A Assetic\Filter\UglifyJs2Filter instance.
     */
    protected function getAssetic_Filter_Uglifyjs2Service()
    {
        $this->services['assetic.filter.uglifyjs2'] = $instance = new \Assetic\Filter\UglifyJs2Filter('/usr/local/bin/uglifyjs', '/usr/bin/nodejs');

        $instance->setTimeout(NULL);
        $instance->setNodePaths(array());
        $instance->setCompress(false);
        $instance->setBeautify(false);
        $instance->setMangle(false);
        $instance->setScrewIe8(false);
        $instance->setComments(false);
        $instance->setWrap(false);
        $instance->setDefines(array());

        return $instance;
    }

    /**
     * Gets the 'assetic.filter_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\AsseticBundle\FilterManager A Symfony\Bundle\AsseticBundle\FilterManager instance.
     */
    protected function getAssetic_FilterManagerService()
    {
        return $this->services['assetic.filter_manager'] = new \Symfony\Bundle\AsseticBundle\FilterManager($this, array('cssrewrite' => 'assetic.filter.cssrewrite', 'uglifycss' => 'assetic.filter.uglifycss', 'uglifyjs2' => 'assetic.filter.uglifyjs2', 'less' => 'assetic.filter.less', 'scssphp' => 'assetic.filter.scssphp'));
    }

    /**
     * Gets the 'assets.context' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Asset\Context\RequestStackContext A Symfony\Component\Asset\Context\RequestStackContext instance.
     */
    protected function getAssets_ContextService()
    {
        return $this->services['assets.context'] = new \Symfony\Component\Asset\Context\RequestStackContext($this->get('request_stack'));
    }

    /**
     * Gets the 'assets.packages' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Asset\Packages A Symfony\Component\Asset\Packages instance.
     */
    protected function getAssets_PackagesService()
    {
        return $this->services['assets.packages'] = new \Symfony\Component\Asset\Packages(new \Symfony\Component\Asset\PathPackage('', new \Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy(), $this->get('assets.context')), array());
    }

    /**
     * Gets the 'cache_clearer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\CacheClearer\ChainCacheClearer A Symfony\Component\HttpKernel\CacheClearer\ChainCacheClearer instance.
     */
    protected function getCacheClearerService()
    {
        return $this->services['cache_clearer'] = new \Symfony\Component\HttpKernel\CacheClearer\ChainCacheClearer(array());
    }

    /**
     * Gets the 'cache_warmer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate A Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate instance.
     */
    protected function getCacheWarmerService()
    {
        $a = $this->get('kernel');
        $b = $this->get('templating.filename_parser');

        $c = new \Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplateFinder($a, $b, ($this->targetDirs[2].'/Resources'));

        return $this->services['cache_warmer'] = new \Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerAggregate(array(0 => new \Doctrine\Bundle\MongoDBBundle\CacheWarmer\ProxyCacheWarmer($this), 1 => new \Doctrine\Bundle\MongoDBBundle\CacheWarmer\HydratorCacheWarmer($this), 2 => new \Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplatePathsCacheWarmer($c, $this->get('templating.locator')), 3 => new \Symfony\Bundle\AsseticBundle\CacheWarmer\AssetManagerCacheWarmer($this), 4 => $this->get('kernel.class_cache.cache_warmer'), 5 => new \Symfony\Bundle\FrameworkBundle\CacheWarmer\TranslationsCacheWarmer($this->get('translator.default')), 6 => new \Symfony\Bundle\FrameworkBundle\CacheWarmer\RouterCacheWarmer($this->get('router')), 7 => new \Symfony\Bundle\TwigBundle\CacheWarmer\TemplateCacheCacheWarmer($this, $c, array()), 8 => new \Symfony\Bundle\TwigBundle\CacheWarmer\TemplateCacheWarmer($this->get('twig'), new \Symfony\Bundle\TwigBundle\TemplateIterator($a, $this->targetDirs[2], array())), 9 => new \JMS\DiExtraBundle\HttpKernel\ControllerInjectorsWarmer($a, $this->get('jms_di_extra.controller_resolver'), array())));
    }

    /**
     * Gets the 'config_cache_factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Config\ResourceCheckerConfigCacheFactory A Symfony\Component\Config\ResourceCheckerConfigCacheFactory instance.
     */
    protected function getConfigCacheFactoryService()
    {
        return $this->services['config_cache_factory'] = new \Symfony\Component\Config\ResourceCheckerConfigCacheFactory(array(0 => new \Symfony\Component\Config\Resource\SelfCheckingResourceChecker(), 1 => new \Symfony\Component\Config\Resource\BCResourceInterfaceChecker()));
    }

    /**
     * Gets the 'data_collector.dump' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\DataCollector\DumpDataCollector A Symfony\Component\HttpKernel\DataCollector\DumpDataCollector instance.
     */
    protected function getDataCollector_DumpService()
    {
        return $this->services['data_collector.dump'] = new \Symfony\Component\HttpKernel\DataCollector\DumpDataCollector($this->get('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE), NULL, 'UTF-8', $this->get('request_stack'), NULL);
    }

    /**
     * Gets the 'data_collector.form' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\DataCollector\FormDataCollector A Symfony\Component\Form\Extension\DataCollector\FormDataCollector instance.
     */
    protected function getDataCollector_FormService()
    {
        return $this->services['data_collector.form'] = new \Symfony\Component\Form\Extension\DataCollector\FormDataCollector($this->get('data_collector.form.extractor'));
    }

    /**
     * Gets the 'data_collector.form.extractor' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\DataCollector\FormDataExtractor A Symfony\Component\Form\Extension\DataCollector\FormDataExtractor instance.
     */
    protected function getDataCollector_Form_ExtractorService()
    {
        return $this->services['data_collector.form.extractor'] = new \Symfony\Component\Form\Extension\DataCollector\FormDataExtractor();
    }

    /**
     * Gets the 'data_collector.request' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\DataCollector\RequestDataCollector A Symfony\Component\HttpKernel\DataCollector\RequestDataCollector instance.
     */
    protected function getDataCollector_RequestService()
    {
        return $this->services['data_collector.request'] = new \Symfony\Component\HttpKernel\DataCollector\RequestDataCollector();
    }

    /**
     * Gets the 'data_collector.router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\DataCollector\RouterDataCollector A Symfony\Bundle\FrameworkBundle\DataCollector\RouterDataCollector instance.
     */
    protected function getDataCollector_RouterService()
    {
        return $this->services['data_collector.router'] = new \Symfony\Bundle\FrameworkBundle\DataCollector\RouterDataCollector();
    }

    /**
     * Gets the 'data_collector.translation' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\DataCollector\TranslationDataCollector A Symfony\Component\Translation\DataCollector\TranslationDataCollector instance.
     */
    protected function getDataCollector_TranslationService()
    {
        return $this->services['data_collector.translation'] = new \Symfony\Component\Translation\DataCollector\TranslationDataCollector($this->get('translator'));
    }

    /**
     * Gets the 'debug.controller_resolver' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\Controller\TraceableControllerResolver A Symfony\Component\HttpKernel\Controller\TraceableControllerResolver instance.
     */
    protected function getDebug_ControllerResolverService()
    {
        return $this->services['debug.controller_resolver'] = new \Symfony\Component\HttpKernel\Controller\TraceableControllerResolver($this->get('jms_di_extra.controller_resolver'), $this->get('debug.stopwatch'));
    }

    /**
     * Gets the 'debug.debug_handlers_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\DebugHandlersListener A Symfony\Component\HttpKernel\EventListener\DebugHandlersListener instance.
     */
    protected function getDebug_DebugHandlersListenerService()
    {
        return $this->services['debug.debug_handlers_listener'] = new \Symfony\Component\HttpKernel\EventListener\DebugHandlersListener(NULL, $this->get('monolog.logger.php', ContainerInterface::NULL_ON_INVALID_REFERENCE), NULL, NULL, true, NULL);
    }

    /**
     * Gets the 'debug.dump_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\DumpListener A Symfony\Component\HttpKernel\EventListener\DumpListener instance.
     */
    protected function getDebug_DumpListenerService()
    {
        return $this->services['debug.dump_listener'] = new \Symfony\Component\HttpKernel\EventListener\DumpListener($this->get('var_dumper.cloner'), $this->get('data_collector.dump'));
    }

    /**
     * Gets the 'debug.event_dispatcher' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher A Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher instance.
     */
    protected function getDebug_EventDispatcherService()
    {
        $this->services['debug.event_dispatcher'] = $instance = new \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher(new \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher($this), $this->get('debug.stopwatch'), $this->get('monolog.logger.event', ContainerInterface::NULL_ON_INVALID_REFERENCE));

        $instance->addListenerService('kernel.controller', array(0 => 'data_collector.router', 1 => 'onKernelController'), 0);
        $instance->addListenerService('kernel.request', array(0 => 'knp_menu.listener.voters', 1 => 'onKernelRequest'), 0);
        $instance->addListenerService('kernel.request', array(0 => 'mbh.base_on_request_listener', 1 => 'onKernelRequest'), 0);
        $instance->addListenerService('kernel.controller', array(0 => 'mbh.base_on_controller_listener', 1 => 'onKernelController'), 0);
        $instance->addListenerService('kernel.controller', array(0 => 'mbh.user.work_shift_listener', 1 => 'onKernelController'), 0);
        $instance->addListenerService('kernel.controller', array(0 => 'mbh.check_hotel.action_listener', 1 => 'onKernelController'), 0);
        $instance->addSubscriberService('response_listener', 'Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener');
        $instance->addSubscriberService('streamed_response_listener', 'Symfony\\Component\\HttpKernel\\EventListener\\StreamedResponseListener');
        $instance->addSubscriberService('locale_listener', 'Symfony\\Component\\HttpKernel\\EventListener\\LocaleListener');
        $instance->addSubscriberService('translator_listener', 'Symfony\\Component\\HttpKernel\\EventListener\\TranslatorListener');
        $instance->addSubscriberService('session_listener', 'Symfony\\Bundle\\FrameworkBundle\\EventListener\\SessionListener');
        $instance->addSubscriberService('session.save_listener', 'Symfony\\Component\\HttpKernel\\EventListener\\SaveSessionListener');
        $instance->addSubscriberService('fragment.listener', 'Symfony\\Component\\HttpKernel\\EventListener\\FragmentListener');
        $instance->addSubscriberService('profiler_listener', 'Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener');
        $instance->addSubscriberService('data_collector.request', 'Symfony\\Component\\HttpKernel\\DataCollector\\RequestDataCollector');
        $instance->addSubscriberService('router_listener', 'Symfony\\Component\\HttpKernel\\EventListener\\RouterListener');
        $instance->addSubscriberService('debug.debug_handlers_listener', 'Symfony\\Component\\HttpKernel\\EventListener\\DebugHandlersListener');
        $instance->addSubscriberService('security.firewall', 'Symfony\\Component\\Security\\Http\\Firewall');
        $instance->addSubscriberService('security.rememberme.response_listener', 'Symfony\\Component\\Security\\Http\\RememberMe\\ResponseListener');
        $instance->addSubscriberService('twig.exception_listener', 'Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener');
        $instance->addSubscriberService('monolog.handler.console', 'Symfony\\Bridge\\Monolog\\Handler\\ConsoleHandler');
        $instance->addSubscriberService('swiftmailer.email_sender.listener', 'Symfony\\Bundle\\SwiftmailerBundle\\EventListener\\EmailSenderListener');
        $instance->addSubscriberService('sensio_framework_extra.controller.listener', 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ControllerListener');
        $instance->addSubscriberService('sensio_framework_extra.converter.listener', 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ParamConverterListener');
        $instance->addSubscriberService('sensio_framework_extra.view.listener', 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\TemplateListener');
        $instance->addSubscriberService('sensio_framework_extra.cache.listener', 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\HttpCacheListener');
        $instance->addSubscriberService('sensio_framework_extra.security.listener', 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\SecurityListener');
        $instance->addSubscriberService('stof_doctrine_extensions.event_listener.locale', 'Stof\\DoctrineExtensionsBundle\\EventListener\\LocaleListener');
        $instance->addSubscriberService('stof_doctrine_extensions.event_listener.logger', 'Stof\\DoctrineExtensionsBundle\\EventListener\\LoggerListener');
        $instance->addSubscriberService('stof_doctrine_extensions.event_listener.blame', 'Stof\\DoctrineExtensionsBundle\\EventListener\\BlameListener');
        $instance->addSubscriberService('fos_user.security.interactive_login_listener', 'FOS\\UserBundle\\EventListener\\LastLoginListener');
        $instance->addSubscriberService('fos_user.listener.authentication', 'FOS\\UserBundle\\EventListener\\AuthenticationListener');
        $instance->addSubscriberService('fos_user.listener.flash', 'FOS\\UserBundle\\EventListener\\FlashListener');
        $instance->addSubscriberService('fos_user.listener.resetting', 'FOS\\UserBundle\\EventListener\\ResettingListener');
        $instance->addSubscriberService('web_profiler.debug_toolbar', 'Symfony\\Bundle\\WebProfilerBundle\\EventListener\\WebDebugToolbarListener');
        $instance->addSubscriberService('debug.dump_listener', 'Symfony\\Component\\HttpKernel\\EventListener\\DumpListener');

        return $instance;
    }

    /**
     * Gets the 'debug.stopwatch' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Stopwatch\Stopwatch A Symfony\Component\Stopwatch\Stopwatch instance.
     */
    protected function getDebug_StopwatchService()
    {
        return $this->services['debug.stopwatch'] = new \Symfony\Component\Stopwatch\Stopwatch();
    }

    /**
     * Gets the 'doctrine_mongo_db_param_converter' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter A Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter instance.
     */
    protected function getDoctrineMongoDbParamConverterService()
    {
        return $this->services['doctrine_mongo_db_param_converter'] = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter($this->get('doctrine_mongodb'));
    }

    /**
     * Gets the 'doctrine_mongodb' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Bundle\MongoDBBundle\ManagerRegistry A Doctrine\Bundle\MongoDBBundle\ManagerRegistry instance.
     */
    protected function getDoctrineMongodbService()
    {
        $this->services['doctrine_mongodb'] = $instance = new \Doctrine\Bundle\MongoDBBundle\ManagerRegistry('MongoDB', array('default' => 'doctrine_mongodb.odm.default_connection'), array('default' => 'doctrine_mongodb.odm.default_document_manager'), 'default', 'default', 'Doctrine\\ODM\\MongoDB\\Proxy\\Proxy');

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'doctrine_mongodb.odm.cache.array' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Common\Cache\ArrayCache A Doctrine\Common\Cache\ArrayCache instance.
     */
    protected function getDoctrineMongodb_Odm_Cache_ArrayService()
    {
        return $this->services['doctrine_mongodb.odm.cache.array'] = new \Doctrine\Common\Cache\ArrayCache();
    }

    /**
     * Gets the 'doctrine_mongodb.odm.default_configuration' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\ODM\MongoDB\Configuration A Doctrine\ODM\MongoDB\Configuration instance.
     */
    protected function getDoctrineMongodb_Odm_DefaultConfigurationService()
    {
        $a = $this->get('annotation_reader');

        $b = new \Doctrine\Common\Cache\ArrayCache();
        $b->setNamespace('sf2mongodb_default_f9a20825c05ccb49f676c9ff69d55cab6166cb7c0f594eb2be76d5be6091ff0f');

        $c = new \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver($a, array(0 => ($this->targetDirs[3].'/vendor/gedmo/doctrine-extensions/lib/Gedmo/Loggable/Document'), 1 => ($this->targetDirs[3].'/vendor/gedmo/doctrine-extensions/lib/Gedmo/Translatable/Document'), 2 => ($this->targetDirs[3].'/src/MBH/Bundle/BaseBundle/Document'), 3 => ($this->targetDirs[3].'/src/MBH/Bundle/UserBundle/Document'), 4 => ($this->targetDirs[3].'/src/MBH/Bundle/HotelBundle/Document'), 5 => ($this->targetDirs[3].'/src/MBH/Bundle/PriceBundle/Document'), 6 => ($this->targetDirs[3].'/src/MBH/Bundle/PackageBundle/Document'), 7 => ($this->targetDirs[3].'/src/MBH/Bundle/CashBundle/Document'), 8 => ($this->targetDirs[3].'/src/MBH/Bundle/ChannelManagerBundle/Document'), 9 => ($this->targetDirs[3].'/src/MBH/Bundle/OnlineBundle/Document'), 10 => ($this->targetDirs[3].'/src/MBH/Bundle/ClientBundle/Document'), 11 => ($this->targetDirs[3].'/src/MBH/Bundle/VegaBundle/Document'), 12 => ($this->targetDirs[3].'/src/MBH/Bundle/WarehouseBundle/Document'), 13 => ($this->targetDirs[3].'/src/MBH/Bundle/RestaurantBundle/Document')));

        $d = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();
        $d->addDriver($c, 'Gedmo\\Loggable\\Document');
        $d->addDriver($c, 'Gedmo\\Translatable\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\BaseBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\UserBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\HotelBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\PriceBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\PackageBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\CashBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\ChannelManagerBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\OnlineBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\ClientBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\VegaBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\WarehouseBundle\\Document');
        $d->addDriver($c, 'MBH\\Bundle\\RestaurantBundle\\Document');
        $d->addDriver(new \Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver(new \Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator(array(($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/config/doctrine-mapping') => 'FOS\\UserBundle\\Model'), '.mongodb.xml')), 'FOS\\UserBundle\\Model');

        $e = new \Doctrine\Bundle\MongoDBBundle\Logger\Logger($this->get('monolog.logger.doctrine', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $e->setBatchInsertThreshold(4);

        $this->services['doctrine_mongodb.odm.default_configuration'] = $instance = new \Doctrine\ODM\MongoDB\Configuration();

        $instance->setDocumentNamespaces(array('GedmoLoggable' => 'Gedmo\\Loggable\\Document', 'GedmoTranslatable' => 'Gedmo\\Translatable\\Document', 'MBHBaseBundle' => 'MBH\\Bundle\\BaseBundle\\Document', 'MBHUserBundle' => 'MBH\\Bundle\\UserBundle\\Document', 'MBHHotelBundle' => 'MBH\\Bundle\\HotelBundle\\Document', 'MBHPriceBundle' => 'MBH\\Bundle\\PriceBundle\\Document', 'MBHPackageBundle' => 'MBH\\Bundle\\PackageBundle\\Document', 'MBHCashBundle' => 'MBH\\Bundle\\CashBundle\\Document', 'MBHChannelManagerBundle' => 'MBH\\Bundle\\ChannelManagerBundle\\Document', 'MBHOnlineBundle' => 'MBH\\Bundle\\OnlineBundle\\Document', 'MBHClientBundle' => 'MBH\\Bundle\\ClientBundle\\Document', 'MBHVegaBundle' => 'MBH\\Bundle\\VegaBundle\\Document', 'MBHWarehouseBundle' => 'MBH\\Bundle\\WarehouseBundle\\Document', 'MBHRestaurantBundle' => 'MBH\\Bundle\\RestaurantBundle\\Document'));
        $instance->addFilter('softdeleteable', 'Gedmo\\SoftDeleteable\\Filter\\ODM\\SoftDeleteableFilter', array());
        $instance->addFilter('hotelable', 'MBH\\Bundle\\BaseBundle\\Filter\\HotelableFilter', array());
        $instance->setMetadataCacheImpl($b);
        $instance->setMetadataDriverImpl($d);
        $instance->setProxyDir((__DIR__.'/doctrine/odm/mongodb/Proxies'));
        $instance->setProxyNamespace('MongoDBODMProxies');
        $instance->setAutoGenerateProxyClasses(0);
        $instance->setHydratorDir((__DIR__.'/doctrine/odm/mongodb/Hydrators'));
        $instance->setHydratorNamespace('Hydrators');
        $instance->setAutoGenerateHydratorClasses(0);
        $instance->setDefaultDB('mbh');
        $instance->setDefaultCommitOptions(array());
        $instance->setRetryConnect(0);
        $instance->setRetryQuery(0);
        $instance->setDefaultRepositoryClassName('Doctrine\\ODM\\MongoDB\\DocumentRepository');
        $instance->setLoggerCallable(array(0 => new \Doctrine\Bundle\MongoDBBundle\Logger\AggregateLogger(array(0 => $e, 1 => $this->get('doctrine_mongodb.odm.data_collector.pretty'))), 1 => 'logQuery'));

        return $instance;
    }

    /**
     * Gets the 'doctrine_mongodb.odm.default_connection' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\MongoDB\Connection A Doctrine\MongoDB\Connection instance.
     */
    protected function getDoctrineMongodb_Odm_DefaultConnectionService()
    {
        return $this->services['doctrine_mongodb.odm.default_connection'] = new \Doctrine\MongoDB\Connection('mongodb://localhost:27017/mbh', array(), $this->get('doctrine_mongodb.odm.default_configuration'), $this->get('doctrine_mongodb.odm.event_manager'));
    }

    /**
     * Gets the 'doctrine_mongodb.odm.default_document_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\ODM\MongoDB\DocumentManager A Doctrine\ODM\MongoDB\DocumentManager instance.
     */
    protected function getDoctrineMongodb_Odm_DefaultDocumentManagerService()
    {
        $this->services['doctrine_mongodb.odm.default_document_manager'] = $instance = \Doctrine\ODM\MongoDB\DocumentManager::create($this->get('doctrine_mongodb.odm.default_connection'), $this->get('doctrine_mongodb.odm.default_configuration'), $this->get('doctrine_mongodb.odm.event_manager'));

        $this->get('doctrine_mongodb.odm.default_manager_configurator')->configure($instance);

        return $instance;
    }

    /**
     * Gets the 'doctrine_mongodb.odm.default_manager_configurator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Bundle\MongoDBBundle\ManagerConfigurator A Doctrine\Bundle\MongoDBBundle\ManagerConfigurator instance.
     */
    protected function getDoctrineMongodb_Odm_DefaultManagerConfiguratorService()
    {
        return $this->services['doctrine_mongodb.odm.default_manager_configurator'] = new \Doctrine\Bundle\MongoDBBundle\ManagerConfigurator(array(0 => 'softdeleteable'));
    }

    /**
     * Gets the 'doctrine_mongodb.odm.event_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Doctrine\ContainerAwareEventManager A Symfony\Bridge\Doctrine\ContainerAwareEventManager instance.
     */
    protected function getDoctrineMongodb_Odm_EventManagerService()
    {
        $a = $this->get('annotation_reader');

        $b = new \Gedmo\SoftDeleteable\SoftDeleteableListener();
        $b->setAnnotationReader($a);

        $c = new \Gedmo\Timestampable\TimestampableListener();
        $c->setAnnotationReader($a);

        $this->services['doctrine_mongodb.odm.event_manager'] = $instance = new \Symfony\Bridge\Doctrine\ContainerAwareEventManager($this);

        $instance->addEventSubscriber($this->get('mbh.hotel.facility.subscriber'));
        $instance->addEventSubscriber($this->get('mbh.testaurant.subscriber'));
        $instance->addEventSubscriber($this->get('mbh.room_cache.subscriber'));
        $instance->addEventSubscriber($this->get('mbh.package.subscriber'));
        $instance->addEventSubscriber($this->get('mbh.hotel.room.subscriber'));
        $instance->addEventSubscriber($this->get('mbh.tariff.subscriber'));
        $instance->addEventSubscriber($this->get('mbh.warehouse.subscriber'));
        $instance->addEventSubscriber($this->get('stof_doctrine_extensions.listener.loggable'));
        $instance->addEventSubscriber($this->get('mbh.event_listener.generate_internationl_listener'));
        $instance->addEventSubscriber($this->get('mbh.cash.document.subscriber'));
        $instance->addEventSubscriber($this->get('mbh.event_listener.versioned_subscriber'));
        $instance->addEventSubscriber($this->get('mbh.event_listener.hotelable_listener'));
        $instance->addEventSubscriber($this->get('mbh.event_listener.check_relation_subscriber'));
        $instance->addEventSubscriber(new \FOS\UserBundle\Doctrine\MongoDB\UserListener($this));
        $instance->addEventSubscriber($b);
        $instance->addEventSubscriber($this->get('mbh.package.order.subscriber'));
        $instance->addEventSubscriber($this->get('mbh.hotel.task.subscriber'));
        $instance->addEventSubscriber($this->get('mbh.package.subscriber.tourist'));
        $instance->addEventSubscriber($this->get('stof_doctrine_extensions.listener.blameable'));
        $instance->addEventSubscriber($this->get('mbh.channelmanager.configs.subscriber'));
        $instance->addEventSubscriber($this->get('mbh.hotel.room_type.subscriber'));
        $instance->addEventSubscriber($c);
        $instance->addEventSubscriber($this->get('stof_doctrine_extensions.listener.translatable'));
        $instance->addEventListener(array(0 => 'loadClassMetadata'), $this->get('mbh.user.metadata_listener'));

        return $instance;
    }

    /**
     * Gets the 'doctrine_mongodb.odm.metadata.annotation' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver A Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver instance.
     */
    protected function getDoctrineMongodb_Odm_Metadata_AnnotationService()
    {
        return $this->services['doctrine_mongodb.odm.metadata.annotation'] = new \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver($this->get('annotation_reader'), array());
    }

    /**
     * Gets the 'doctrine_mongodb.odm.metadata.chain' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain A Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain instance.
     */
    protected function getDoctrineMongodb_Odm_Metadata_ChainService()
    {
        return $this->services['doctrine_mongodb.odm.metadata.chain'] = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();
    }

    /**
     * Gets the 'doctrine_mongodb.odm.metadata.xml' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Bundle\MongoDBBundle\Mapping\Driver\XmlDriver A Doctrine\Bundle\MongoDBBundle\Mapping\Driver\XmlDriver instance.
     */
    protected function getDoctrineMongodb_Odm_Metadata_XmlService()
    {
        return $this->services['doctrine_mongodb.odm.metadata.xml'] = new \Doctrine\Bundle\MongoDBBundle\Mapping\Driver\XmlDriver(array());
    }

    /**
     * Gets the 'doctrine_mongodb.odm.metadata.yml' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Bundle\MongoDBBundle\Mapping\Driver\YamlDriver A Doctrine\Bundle\MongoDBBundle\Mapping\Driver\YamlDriver instance.
     */
    protected function getDoctrineMongodb_Odm_Metadata_YmlService()
    {
        return $this->services['doctrine_mongodb.odm.metadata.yml'] = new \Doctrine\Bundle\MongoDBBundle\Mapping\Driver\YamlDriver(array());
    }

    /**
     * Gets the 'doctrine_odm.mongodb.validator.unique' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Validator\Constraints\UniqueEntityValidator A MBH\Bundle\BaseBundle\Validator\Constraints\UniqueEntityValidator instance.
     */
    protected function getDoctrineOdm_Mongodb_Validator_UniqueService()
    {
        return $this->services['doctrine_odm.mongodb.validator.unique'] = new \MBH\Bundle\BaseBundle\Validator\Constraints\UniqueEntityValidator($this->get('doctrine_mongodb'));
    }

    /**
     * Gets the 'doctrine_odm.mongodb.validator_initializer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Doctrine\Validator\DoctrineInitializer A Symfony\Bridge\Doctrine\Validator\DoctrineInitializer instance.
     */
    protected function getDoctrineOdm_Mongodb_ValidatorInitializerService()
    {
        return $this->services['doctrine_odm.mongodb.validator_initializer'] = new \Symfony\Bridge\Doctrine\Validator\DoctrineInitializer($this->get('doctrine_mongodb'));
    }

    /**
     * Gets the 'file_locator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\Config\FileLocator A Symfony\Component\HttpKernel\Config\FileLocator instance.
     */
    protected function getFileLocatorService()
    {
        return $this->services['file_locator'] = new \Symfony\Component\HttpKernel\Config\FileLocator($this->get('kernel'), ($this->targetDirs[2].'/Resources'));
    }

    /**
     * Gets the 'filesystem' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Filesystem\Filesystem A Symfony\Component\Filesystem\Filesystem instance.
     */
    protected function getFilesystemService()
    {
        return $this->services['filesystem'] = new \Symfony\Component\Filesystem\Filesystem();
    }

    /**
     * Gets the 'form.csrf_provider' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter A Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter instance.
     *
     * @deprecated The "form.csrf_provider" service is deprecated since Symfony 2.4 and will be removed in 3.0. Use the "security.csrf.token_manager" service instead.
     */
    protected function getForm_CsrfProviderService()
    {
        @trigger_error('The "form.csrf_provider" service is deprecated since Symfony 2.4 and will be removed in 3.0. Use the "security.csrf.token_manager" service instead.', E_USER_DEPRECATED);

        return $this->services['form.csrf_provider'] = new \Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter($this->get('security.csrf.token_manager'));
    }

    /**
     * Gets the 'form.factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\FormFactory A Symfony\Component\Form\FormFactory instance.
     */
    protected function getForm_FactoryService()
    {
        return $this->services['form.factory'] = new \Symfony\Component\Form\FormFactory($this->get('form.registry'), $this->get('form.resolved_type_factory'));
    }

    /**
     * Gets the 'form.registry' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\FormRegistry A Symfony\Component\Form\FormRegistry instance.
     */
    protected function getForm_RegistryService()
    {
        return $this->services['form.registry'] = new \Symfony\Component\Form\FormRegistry(array(0 => new \Symfony\Component\Form\Extension\DependencyInjection\DependencyInjectionExtension($this, array('form' => 'form.type.form', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType' => 'form.type.form', 'birthday' => 'form.type.birthday', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\BirthdayType' => 'form.type.birthday', 'checkbox' => 'form.type.checkbox', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType' => 'form.type.checkbox', 'choice' => 'form.type.choice', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType' => 'form.type.choice', 'collection' => 'form.type.collection', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\CollectionType' => 'form.type.collection', 'country' => 'form.type.country', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\CountryType' => 'form.type.country', 'date' => 'form.type.date', 'MBH\\Bundle\\BaseBundle\\Form\\Extension\\DateType' => 'form.type.date', 'datetime' => 'form.type.datetime', 'MBH\\Bundle\\BaseBundle\\Form\\Extension\\DateTimeType' => 'form.type.datetime', 'email' => 'form.type.email', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\EmailType' => 'form.type.email', 'file' => 'form.type.file', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType' => 'form.type.file', 'hidden' => 'form.type.hidden', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType' => 'form.type.hidden', 'integer' => 'form.type.integer', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\IntegerType' => 'form.type.integer', 'mbh_language' => 'form.type.language', 'MBH\\Bundle\\BaseBundle\\Form\\LanguageType' => 'form.type.language', 'locale' => 'form.type.locale', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\LocaleType' => 'form.type.locale', 'money' => 'form.type.money', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\MoneyType' => 'form.type.money', 'number' => 'form.type.number', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\NumberType' => 'form.type.number', 'password' => 'form.type.password', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\PasswordType' => 'form.type.password', 'percent' => 'form.type.percent', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\PercentType' => 'form.type.percent', 'radio' => 'form.type.radio', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\RadioType' => 'form.type.radio', 'range' => 'form.type.range', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\RangeType' => 'form.type.range', 'repeated' => 'form.type.repeated', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\RepeatedType' => 'form.type.repeated', 'search' => 'form.type.search', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\SearchType' => 'form.type.search', 'textarea' => 'form.type.textarea', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\TextareaType' => 'form.type.textarea', 'text' => 'form.type.text', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType' => 'form.type.text', 'time' => 'form.type.time', 'MBH\\Bundle\\BaseBundle\\Form\\Extension\\TimeType' => 'form.type.time', 'timezone' => 'form.type.timezone', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\TimezoneType' => 'form.type.timezone', 'url' => 'form.type.url', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\UrlType' => 'form.type.url', 'button' => 'form.type.button', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ButtonType' => 'form.type.button', 'submit' => 'form.type.submit', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\SubmitType' => 'form.type.submit', 'reset' => 'form.type.reset', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ResetType' => 'form.type.reset', 'currency' => 'form.type.currency', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\CurrencyType' => 'form.type.currency', 'document' => 'form.type.mongodb_document', 'Doctrine\\Bundle\\MongoDBBundle\\Form\\Type\\DocumentType' => 'form.type.mongodb_document', 'fos_user_username' => 'fos_user.username_form_type', 'FOS\\UserBundle\\Form\\Type\\UsernameFormType' => 'fos_user.username_form_type', 'fos_user_profile' => 'fos_user.profile.form.type', 'FOS\\UserBundle\\Form\\Type\\ProfileFormType' => 'fos_user.profile.form.type', 'fos_user_registration' => 'fos_user.registration.form.type', 'FOS\\UserBundle\\Form\\Type\\RegistrationFormType' => 'fos_user.registration.form.type', 'fos_user_change_password' => 'fos_user.change_password.form.type', 'FOS\\UserBundle\\Form\\Type\\ChangePasswordFormType' => 'fos_user.change_password.form.type', 'fos_user_resetting' => 'fos_user.resetting.form.type', 'FOS\\UserBundle\\Form\\Type\\ResettingFormType' => 'fos_user.resetting.form.type', 'fos_user_group' => 'fos_user.group.form.type', 'FOS\\UserBundle\\Form\\Type\\GroupFormType' => 'fos_user.group.form.type', 'liip_imagine_image' => 'liip_imagine.form.type.image', 'Liip\\ImagineBundle\\Form\\Type\\ImageType' => 'liip_imagine.form.type.image', 'mbh_facilities' => 'form.type.facilities', 'MBH\\Bundle\\BaseBundle\\Form\\FacilitiesType' => 'form.type.facilities', 'mbh_bundle_userbundle_grouptype' => 'mbh.user.group.type', 'MBH\\Bundle\\UserBundle\\Form\\GroupType' => 'mbh.user.group.type', 'roles' => 'mbh.user.roles.type', 'MBH\\Bundle\\UserBundle\\Form\\Type\\RolesType' => 'mbh.user.roles.type', 'mbh_birthplace' => 'mbh.package.form.type.birthplace', 'MBH\\Bundle\\PackageBundle\\Form\\BirthplaceType' => 'mbh.package.form.type.birthplace', 'mbh_address_object_decomposed' => 'mbh.package.form.type.address_object_decomposed', 'MBH\\Bundle\\PackageBundle\\Form\\AddressObjectDecomposedType' => 'mbh.package.form.type.address_object_decomposed', 'mbh_document_relation' => 'mbh.package.form.type.document_relation', 'MBH\\Bundle\\PackageBundle\\Form\\DocumentRelationType' => 'mbh.package.form.type.document_relation', 'mbh_bundle_restaurantbundle_dishorder_dishitemembedded_type' => 'mbh__restaurant.form_dish_order.dish_order_item_emmbedded_type', 'MBH\\Bundle\\RestaurantBundle\\Form\\DishOrder\\DishOrderItemEmmbeddedType' => 'mbh__restaurant.form_dish_order.dish_order_item_emmbedded_type', 'mbh_bundle_restaurantbundle_dishmenu_ingredientembedded_type' => 'mbh__restaurant.form.dish_menu_ingredient_embedded_type', 'MBH\\Bundle\\RestaurantBundle\\Form\\DishMenuIngredientEmbeddedType' => 'mbh__restaurant.form.dish_menu_ingredient_embedded_type', 'mbh_bundle_restaurantbundle_dishorder_dishorderitem_type' => 'mbh__restaurant.form_dish_order.dish_order_item_type', 'MBH\\Bundle\\RestaurantBundle\\Form\\DishOrder\\DishOrderItemType' => 'mbh__restaurant.form_dish_order.dish_order_item_type'), array('Symfony\\Component\\Form\\Extension\\Core\\Type\\FormType' => array(0 => 'form.type_extension.form.http_foundation', 1 => 'form.type_extension.form.validator', 2 => 'form.type_extension.csrf', 3 => 'form.type_extension.form.data_collector'), 'Symfony\\Component\\Form\\Extension\\Core\\Type\\RepeatedType' => array(0 => 'form.type_extension.repeated.validator'), 'Symfony\\Component\\Form\\Extension\\Core\\Type\\SubmitType' => array(0 => 'form.type_extension.submit.validator'), 'form' => array(0 => 'mbh.form.fieldset_extension', 1 => 'mbh.form.help_extension', 2 => 'mbh.form.bottom_extension')), array(0 => 'form.type_guesser.validator', 1 => 'form.type_guesser.doctrine.mongodb'))), $this->get('form.resolved_type_factory'));
    }

    /**
     * Gets the 'form.resolved_type_factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\DataCollector\Proxy\ResolvedTypeFactoryDataCollectorProxy A Symfony\Component\Form\Extension\DataCollector\Proxy\ResolvedTypeFactoryDataCollectorProxy instance.
     */
    protected function getForm_ResolvedTypeFactoryService()
    {
        return $this->services['form.resolved_type_factory'] = new \Symfony\Component\Form\Extension\DataCollector\Proxy\ResolvedTypeFactoryDataCollectorProxy(new \Symfony\Component\Form\ResolvedFormTypeFactory(), $this->get('data_collector.form'));
    }

    /**
     * Gets the 'form.type.birthday' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\BirthdayType A Symfony\Component\Form\Extension\Core\Type\BirthdayType instance.
     */
    protected function getForm_Type_BirthdayService()
    {
        return $this->services['form.type.birthday'] = new \Symfony\Component\Form\Extension\Core\Type\BirthdayType();
    }

    /**
     * Gets the 'form.type.button' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\ButtonType A Symfony\Component\Form\Extension\Core\Type\ButtonType instance.
     */
    protected function getForm_Type_ButtonService()
    {
        return $this->services['form.type.button'] = new \Symfony\Component\Form\Extension\Core\Type\ButtonType();
    }

    /**
     * Gets the 'form.type.checkbox' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\CheckboxType A Symfony\Component\Form\Extension\Core\Type\CheckboxType instance.
     */
    protected function getForm_Type_CheckboxService()
    {
        return $this->services['form.type.checkbox'] = new \Symfony\Component\Form\Extension\Core\Type\CheckboxType();
    }

    /**
     * Gets the 'form.type.choice' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\ChoiceType A Symfony\Component\Form\Extension\Core\Type\ChoiceType instance.
     */
    protected function getForm_Type_ChoiceService()
    {
        return $this->services['form.type.choice'] = new \Symfony\Component\Form\Extension\Core\Type\ChoiceType();
    }

    /**
     * Gets the 'form.type.collection' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\CollectionType A Symfony\Component\Form\Extension\Core\Type\CollectionType instance.
     */
    protected function getForm_Type_CollectionService()
    {
        return $this->services['form.type.collection'] = new \Symfony\Component\Form\Extension\Core\Type\CollectionType();
    }

    /**
     * Gets the 'form.type.country' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\CountryType A Symfony\Component\Form\Extension\Core\Type\CountryType instance.
     */
    protected function getForm_Type_CountryService()
    {
        return $this->services['form.type.country'] = new \Symfony\Component\Form\Extension\Core\Type\CountryType();
    }

    /**
     * Gets the 'form.type.currency' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\CurrencyType A Symfony\Component\Form\Extension\Core\Type\CurrencyType instance.
     */
    protected function getForm_Type_CurrencyService()
    {
        return $this->services['form.type.currency'] = new \Symfony\Component\Form\Extension\Core\Type\CurrencyType();
    }

    /**
     * Gets the 'form.type.date' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Form\Extension\DateType A MBH\Bundle\BaseBundle\Form\Extension\DateType instance.
     */
    protected function getForm_Type_DateService()
    {
        return $this->services['form.type.date'] = new \MBH\Bundle\BaseBundle\Form\Extension\DateType();
    }

    /**
     * Gets the 'form.type.datetime' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Form\Extension\DateTimeType A MBH\Bundle\BaseBundle\Form\Extension\DateTimeType instance.
     */
    protected function getForm_Type_DatetimeService()
    {
        return $this->services['form.type.datetime'] = new \MBH\Bundle\BaseBundle\Form\Extension\DateTimeType();
    }

    /**
     * Gets the 'form.type.email' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\EmailType A Symfony\Component\Form\Extension\Core\Type\EmailType instance.
     */
    protected function getForm_Type_EmailService()
    {
        return $this->services['form.type.email'] = new \Symfony\Component\Form\Extension\Core\Type\EmailType();
    }

    /**
     * Gets the 'form.type.facilities' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Form\FacilitiesType A MBH\Bundle\BaseBundle\Form\FacilitiesType instance.
     */
    protected function getForm_Type_FacilitiesService()
    {
        $this->services['form.type.facilities'] = $instance = new \MBH\Bundle\BaseBundle\Form\FacilitiesType();

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'form.type.file' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\FileType A Symfony\Component\Form\Extension\Core\Type\FileType instance.
     */
    protected function getForm_Type_FileService()
    {
        return $this->services['form.type.file'] = new \Symfony\Component\Form\Extension\Core\Type\FileType();
    }

    /**
     * Gets the 'form.type.form' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\FormType A Symfony\Component\Form\Extension\Core\Type\FormType instance.
     */
    protected function getForm_Type_FormService()
    {
        return $this->services['form.type.form'] = new \Symfony\Component\Form\Extension\Core\Type\FormType($this->get('property_accessor'));
    }

    /**
     * Gets the 'form.type.hidden' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\HiddenType A Symfony\Component\Form\Extension\Core\Type\HiddenType instance.
     */
    protected function getForm_Type_HiddenService()
    {
        return $this->services['form.type.hidden'] = new \Symfony\Component\Form\Extension\Core\Type\HiddenType();
    }

    /**
     * Gets the 'form.type.integer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\IntegerType A Symfony\Component\Form\Extension\Core\Type\IntegerType instance.
     */
    protected function getForm_Type_IntegerService()
    {
        return $this->services['form.type.integer'] = new \Symfony\Component\Form\Extension\Core\Type\IntegerType();
    }

    /**
     * Gets the 'form.type.language' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Form\LanguageType A MBH\Bundle\BaseBundle\Form\LanguageType instance.
     */
    protected function getForm_Type_LanguageService()
    {
        $this->services['form.type.language'] = $instance = new \MBH\Bundle\BaseBundle\Form\LanguageType();

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'form.type.locale' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\LocaleType A Symfony\Component\Form\Extension\Core\Type\LocaleType instance.
     */
    protected function getForm_Type_LocaleService()
    {
        return $this->services['form.type.locale'] = new \Symfony\Component\Form\Extension\Core\Type\LocaleType();
    }

    /**
     * Gets the 'form.type.money' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\MoneyType A Symfony\Component\Form\Extension\Core\Type\MoneyType instance.
     */
    protected function getForm_Type_MoneyService()
    {
        return $this->services['form.type.money'] = new \Symfony\Component\Form\Extension\Core\Type\MoneyType();
    }

    /**
     * Gets the 'form.type.mongodb_document' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType A Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType instance.
     */
    protected function getForm_Type_MongodbDocumentService()
    {
        return $this->services['form.type.mongodb_document'] = new \Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType($this->get('doctrine_mongodb'));
    }

    /**
     * Gets the 'form.type.number' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\NumberType A Symfony\Component\Form\Extension\Core\Type\NumberType instance.
     */
    protected function getForm_Type_NumberService()
    {
        return $this->services['form.type.number'] = new \Symfony\Component\Form\Extension\Core\Type\NumberType();
    }

    /**
     * Gets the 'form.type.password' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\PasswordType A Symfony\Component\Form\Extension\Core\Type\PasswordType instance.
     */
    protected function getForm_Type_PasswordService()
    {
        return $this->services['form.type.password'] = new \Symfony\Component\Form\Extension\Core\Type\PasswordType();
    }

    /**
     * Gets the 'form.type.percent' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\PercentType A Symfony\Component\Form\Extension\Core\Type\PercentType instance.
     */
    protected function getForm_Type_PercentService()
    {
        return $this->services['form.type.percent'] = new \Symfony\Component\Form\Extension\Core\Type\PercentType();
    }

    /**
     * Gets the 'form.type.radio' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\RadioType A Symfony\Component\Form\Extension\Core\Type\RadioType instance.
     */
    protected function getForm_Type_RadioService()
    {
        return $this->services['form.type.radio'] = new \Symfony\Component\Form\Extension\Core\Type\RadioType();
    }

    /**
     * Gets the 'form.type.range' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\RangeType A Symfony\Component\Form\Extension\Core\Type\RangeType instance.
     */
    protected function getForm_Type_RangeService()
    {
        return $this->services['form.type.range'] = new \Symfony\Component\Form\Extension\Core\Type\RangeType();
    }

    /**
     * Gets the 'form.type.repeated' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\RepeatedType A Symfony\Component\Form\Extension\Core\Type\RepeatedType instance.
     */
    protected function getForm_Type_RepeatedService()
    {
        return $this->services['form.type.repeated'] = new \Symfony\Component\Form\Extension\Core\Type\RepeatedType();
    }

    /**
     * Gets the 'form.type.reset' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\ResetType A Symfony\Component\Form\Extension\Core\Type\ResetType instance.
     */
    protected function getForm_Type_ResetService()
    {
        return $this->services['form.type.reset'] = new \Symfony\Component\Form\Extension\Core\Type\ResetType();
    }

    /**
     * Gets the 'form.type.search' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\SearchType A Symfony\Component\Form\Extension\Core\Type\SearchType instance.
     */
    protected function getForm_Type_SearchService()
    {
        return $this->services['form.type.search'] = new \Symfony\Component\Form\Extension\Core\Type\SearchType();
    }

    /**
     * Gets the 'form.type.submit' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\SubmitType A Symfony\Component\Form\Extension\Core\Type\SubmitType instance.
     */
    protected function getForm_Type_SubmitService()
    {
        return $this->services['form.type.submit'] = new \Symfony\Component\Form\Extension\Core\Type\SubmitType();
    }

    /**
     * Gets the 'form.type.text' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\TextType A Symfony\Component\Form\Extension\Core\Type\TextType instance.
     */
    protected function getForm_Type_TextService()
    {
        return $this->services['form.type.text'] = new \Symfony\Component\Form\Extension\Core\Type\TextType();
    }

    /**
     * Gets the 'form.type.textarea' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\TextareaType A Symfony\Component\Form\Extension\Core\Type\TextareaType instance.
     */
    protected function getForm_Type_TextareaService()
    {
        return $this->services['form.type.textarea'] = new \Symfony\Component\Form\Extension\Core\Type\TextareaType();
    }

    /**
     * Gets the 'form.type.time' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Form\Extension\TimeType A MBH\Bundle\BaseBundle\Form\Extension\TimeType instance.
     */
    protected function getForm_Type_TimeService()
    {
        return $this->services['form.type.time'] = new \MBH\Bundle\BaseBundle\Form\Extension\TimeType();
    }

    /**
     * Gets the 'form.type.timezone' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\TimezoneType A Symfony\Component\Form\Extension\Core\Type\TimezoneType instance.
     */
    protected function getForm_Type_TimezoneService()
    {
        return $this->services['form.type.timezone'] = new \Symfony\Component\Form\Extension\Core\Type\TimezoneType();
    }

    /**
     * Gets the 'form.type.url' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Core\Type\UrlType A Symfony\Component\Form\Extension\Core\Type\UrlType instance.
     */
    protected function getForm_Type_UrlService()
    {
        return $this->services['form.type.url'] = new \Symfony\Component\Form\Extension\Core\Type\UrlType();
    }

    /**
     * Gets the 'form.type_extension.csrf' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Csrf\Type\FormTypeCsrfExtension A Symfony\Component\Form\Extension\Csrf\Type\FormTypeCsrfExtension instance.
     */
    protected function getForm_TypeExtension_CsrfService()
    {
        return $this->services['form.type_extension.csrf'] = new \Symfony\Component\Form\Extension\Csrf\Type\FormTypeCsrfExtension($this->get('security.csrf.token_manager'), true, '_token', $this->get('translator.default'), 'validators');
    }

    /**
     * Gets the 'form.type_extension.form.data_collector' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\DataCollector\Type\DataCollectorTypeExtension A Symfony\Component\Form\Extension\DataCollector\Type\DataCollectorTypeExtension instance.
     */
    protected function getForm_TypeExtension_Form_DataCollectorService()
    {
        return $this->services['form.type_extension.form.data_collector'] = new \Symfony\Component\Form\Extension\DataCollector\Type\DataCollectorTypeExtension($this->get('data_collector.form'));
    }

    /**
     * Gets the 'form.type_extension.form.http_foundation' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\HttpFoundation\Type\FormTypeHttpFoundationExtension A Symfony\Component\Form\Extension\HttpFoundation\Type\FormTypeHttpFoundationExtension instance.
     */
    protected function getForm_TypeExtension_Form_HttpFoundationService()
    {
        return $this->services['form.type_extension.form.http_foundation'] = new \Symfony\Component\Form\Extension\HttpFoundation\Type\FormTypeHttpFoundationExtension(new \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler(new \Symfony\Component\Form\Util\ServerParams($this->get('request_stack'))));
    }

    /**
     * Gets the 'form.type_extension.form.validator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension A Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension instance.
     */
    protected function getForm_TypeExtension_Form_ValidatorService()
    {
        return $this->services['form.type_extension.form.validator'] = new \Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension($this->get('validator'));
    }

    /**
     * Gets the 'form.type_extension.repeated.validator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Validator\Type\RepeatedTypeValidatorExtension A Symfony\Component\Form\Extension\Validator\Type\RepeatedTypeValidatorExtension instance.
     */
    protected function getForm_TypeExtension_Repeated_ValidatorService()
    {
        return $this->services['form.type_extension.repeated.validator'] = new \Symfony\Component\Form\Extension\Validator\Type\RepeatedTypeValidatorExtension();
    }

    /**
     * Gets the 'form.type_extension.submit.validator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Validator\Type\SubmitTypeValidatorExtension A Symfony\Component\Form\Extension\Validator\Type\SubmitTypeValidatorExtension instance.
     */
    protected function getForm_TypeExtension_Submit_ValidatorService()
    {
        return $this->services['form.type_extension.submit.validator'] = new \Symfony\Component\Form\Extension\Validator\Type\SubmitTypeValidatorExtension();
    }

    /**
     * Gets the 'form.type_guesser.doctrine.mongodb' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Bundle\MongoDBBundle\Form\DoctrineMongoDBTypeGuesser A Doctrine\Bundle\MongoDBBundle\Form\DoctrineMongoDBTypeGuesser instance.
     */
    protected function getForm_TypeGuesser_Doctrine_MongodbService()
    {
        return $this->services['form.type_guesser.doctrine.mongodb'] = new \Doctrine\Bundle\MongoDBBundle\Form\DoctrineMongoDBTypeGuesser($this->get('doctrine_mongodb'));
    }

    /**
     * Gets the 'form.type_guesser.validator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser A Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser instance.
     */
    protected function getForm_TypeGuesser_ValidatorService()
    {
        return $this->services['form.type_guesser.validator'] = new \Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser($this->get('validator'));
    }

    /**
     * Gets the 'fos_js_routing.controller' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\JsRoutingBundle\Controller\Controller A FOS\JsRoutingBundle\Controller\Controller instance.
     */
    protected function getFosJsRouting_ControllerService()
    {
        return $this->services['fos_js_routing.controller'] = new \FOS\JsRoutingBundle\Controller\Controller($this->get('fos_js_routing.serializer'), $this->get('fos_js_routing.extractor'), array('enabled' => false), true);
    }

    /**
     * Gets the 'fos_js_routing.extractor' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor A FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor instance.
     */
    protected function getFosJsRouting_ExtractorService()
    {
        return $this->services['fos_js_routing.extractor'] = new \FOS\JsRoutingBundle\Extractor\ExposedRoutesExtractor($this->get('router'), array(), __DIR__, array('FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle', 'SecurityBundle' => 'Symfony\\Bundle\\SecurityBundle\\SecurityBundle', 'TwigBundle' => 'Symfony\\Bundle\\TwigBundle\\TwigBundle', 'MonologBundle' => 'Symfony\\Bundle\\MonologBundle\\MonologBundle', 'SwiftmailerBundle' => 'Symfony\\Bundle\\SwiftmailerBundle\\SwiftmailerBundle', 'AsseticBundle' => 'Symfony\\Bundle\\AsseticBundle\\AsseticBundle', 'SensioFrameworkExtraBundle' => 'Sensio\\Bundle\\FrameworkExtraBundle\\SensioFrameworkExtraBundle', 'DoctrineMongoDBBundle' => 'Doctrine\\Bundle\\MongoDBBundle\\DoctrineMongoDBBundle', 'StofDoctrineExtensionsBundle' => 'Stof\\DoctrineExtensionsBundle\\StofDoctrineExtensionsBundle', 'FOSUserBundle' => 'FOS\\UserBundle\\FOSUserBundle', 'FOSJsRoutingBundle' => 'FOS\\JsRoutingBundle\\FOSJsRoutingBundle', 'KnpMenuBundle' => 'Knp\\Bundle\\MenuBundle\\KnpMenuBundle', 'ObHighchartsBundle' => 'Ob\\HighchartsBundle\\ObHighchartsBundle', 'KnpSnappyBundle' => 'Knp\\Bundle\\SnappyBundle\\KnpSnappyBundle', 'MisdGuzzleBundle' => 'Misd\\GuzzleBundle\\MisdGuzzleBundle', 'IamPersistentMongoDBAclBundle' => 'IamPersistent\\MongoDBAclBundle\\IamPersistentMongoDBAclBundle', 'LiipImagineBundle' => 'Liip\\ImagineBundle\\LiipImagineBundle', 'JMSDiExtraBundle' => 'JMS\\DiExtraBundle\\JMSDiExtraBundle', 'JMSAopBundle' => 'JMS\\AopBundle\\JMSAopBundle', 'JMSTranslationBundle' => 'JMS\\TranslationBundle\\JMSTranslationBundle', 'LiuggioExcelBundle' => 'Liuggio\\ExcelBundle\\LiuggioExcelBundle', 'OrnicarGravatarBundle' => 'Ornicar\\GravatarBundle\\OrnicarGravatarBundle', 'DoctrineFixturesBundle' => 'Doctrine\\Bundle\\FixturesBundle\\DoctrineFixturesBundle', 'LswMemcacheBundle' => 'Lsw\\MemcacheBundle\\LswMemcacheBundle', 'MBHBaseBundle' => 'MBH\\Bundle\\BaseBundle\\MBHBaseBundle', 'MBHUserBundle' => 'MBH\\Bundle\\UserBundle\\MBHUserBundle', 'MBHHotelBundle' => 'MBH\\Bundle\\HotelBundle\\MBHHotelBundle', 'MBHPriceBundle' => 'MBH\\Bundle\\PriceBundle\\MBHPriceBundle', 'MBHPackageBundle' => 'MBH\\Bundle\\PackageBundle\\MBHPackageBundle', 'MBHCashBundle' => 'MBH\\Bundle\\CashBundle\\MBHCashBundle', 'MBHChannelManagerBundle' => 'MBH\\Bundle\\ChannelManagerBundle\\MBHChannelManagerBundle', 'MBHOnlineBundle' => 'MBH\\Bundle\\OnlineBundle\\MBHOnlineBundle', 'MBHDemoBundle' => 'MBH\\Bundle\\DemoBundle\\MBHDemoBundle', 'MBHClientBundle' => 'MBH\\Bundle\\ClientBundle\\MBHClientBundle', 'MBHVegaBundle' => 'MBH\\Bundle\\VegaBundle\\MBHVegaBundle', 'MBHWarehouseBundle' => 'MBH\\Bundle\\WarehouseBundle\\MBHWarehouseBundle', 'MBHRestaurantBundle' => 'MBH\\Bundle\\RestaurantBundle\\MBHRestaurantBundle', 'WebProfilerBundle' => 'Symfony\\Bundle\\WebProfilerBundle\\WebProfilerBundle', 'SensioDistributionBundle' => 'Sensio\\Bundle\\DistributionBundle\\SensioDistributionBundle', 'SensioGeneratorBundle' => 'Sensio\\Bundle\\GeneratorBundle\\SensioGeneratorBundle', 'DebugBundle' => 'Symfony\\Bundle\\DebugBundle\\DebugBundle'));
    }

    /**
     * Gets the 'fos_js_routing.serializer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Serializer\Serializer A Symfony\Component\Serializer\Serializer instance.
     */
    protected function getFosJsRouting_SerializerService()
    {
        return $this->services['fos_js_routing.serializer'] = new \Symfony\Component\Serializer\Serializer(array(0 => new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer()), array('json' => new \Symfony\Component\Serializer\Encoder\JsonEncoder()));
    }

    /**
     * Gets the 'fos_user.change_password.form.factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Factory\FormFactory A FOS\UserBundle\Form\Factory\FormFactory instance.
     */
    protected function getFosUser_ChangePassword_Form_FactoryService()
    {
        return $this->services['fos_user.change_password.form.factory'] = new \FOS\UserBundle\Form\Factory\FormFactory($this->get('form.factory'), 'fos_user_change_password_form', 'fos_user_change_password', array(0 => 'ChangePassword', 1 => 'Default'));
    }

    /**
     * Gets the 'fos_user.change_password.form.type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Type\ChangePasswordFormType A FOS\UserBundle\Form\Type\ChangePasswordFormType instance.
     */
    protected function getFosUser_ChangePassword_Form_TypeService()
    {
        return $this->services['fos_user.change_password.form.type'] = new \FOS\UserBundle\Form\Type\ChangePasswordFormType('MBH\\Bundle\\UserBundle\\Document\\User');
    }

    /**
     * Gets the 'fos_user.group.form.factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Factory\FormFactory A FOS\UserBundle\Form\Factory\FormFactory instance.
     */
    protected function getFosUser_Group_Form_FactoryService()
    {
        return $this->services['fos_user.group.form.factory'] = new \FOS\UserBundle\Form\Factory\FormFactory($this->get('form.factory'), 'fos_user_group_form', 'fos_user_group', array(0 => 'Registration', 1 => 'Default'));
    }

    /**
     * Gets the 'fos_user.group.form.type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Type\GroupFormType A FOS\UserBundle\Form\Type\GroupFormType instance.
     */
    protected function getFosUser_Group_Form_TypeService()
    {
        return $this->services['fos_user.group.form.type'] = new \FOS\UserBundle\Form\Type\GroupFormType('MBH\\Bundle\\UserBundle\\Document\\Group');
    }

    /**
     * Gets the 'fos_user.group_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Doctrine\GroupManager A FOS\UserBundle\Doctrine\GroupManager instance.
     */
    protected function getFosUser_GroupManagerService()
    {
        return $this->services['fos_user.group_manager'] = new \FOS\UserBundle\Doctrine\GroupManager($this->get('fos_user.document_manager'), 'MBH\\Bundle\\UserBundle\\Document\\Group');
    }

    /**
     * Gets the 'fos_user.listener.authentication' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\EventListener\AuthenticationListener A FOS\UserBundle\EventListener\AuthenticationListener instance.
     */
    protected function getFosUser_Listener_AuthenticationService()
    {
        return $this->services['fos_user.listener.authentication'] = new \FOS\UserBundle\EventListener\AuthenticationListener($this->get('fos_user.security.login_manager'), 'main');
    }

    /**
     * Gets the 'fos_user.listener.flash' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\EventListener\FlashListener A FOS\UserBundle\EventListener\FlashListener instance.
     */
    protected function getFosUser_Listener_FlashService()
    {
        return $this->services['fos_user.listener.flash'] = new \FOS\UserBundle\EventListener\FlashListener($this->get('session'), $this->get('translator'));
    }

    /**
     * Gets the 'fos_user.listener.resetting' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\EventListener\ResettingListener A FOS\UserBundle\EventListener\ResettingListener instance.
     */
    protected function getFosUser_Listener_ResettingService()
    {
        return $this->services['fos_user.listener.resetting'] = new \FOS\UserBundle\EventListener\ResettingListener($this->get('router'), 86400);
    }

    /**
     * Gets the 'fos_user.mailer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Mailer\Mailer A FOS\UserBundle\Mailer\Mailer instance.
     */
    protected function getFosUser_MailerService()
    {
        return $this->services['fos_user.mailer'] = new \FOS\UserBundle\Mailer\Mailer($this->get('swiftmailer.mailer.default'), $this->get('router'), $this->get('templating'), array('confirmation.template' => 'FOSUserBundle:Registration:email.txt.twig', 'resetting.template' => 'FOSUserBundle:Resetting:email.txt.twig', 'from_email' => array('confirmation' => array('webmaster@example.com' => 'webmaster'), 'resetting' => array('webmaster@example.com' => 'webmaster'))));
    }

    /**
     * Gets the 'fos_user.profile.form.factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Factory\FormFactory A FOS\UserBundle\Form\Factory\FormFactory instance.
     */
    protected function getFosUser_Profile_Form_FactoryService()
    {
        return $this->services['fos_user.profile.form.factory'] = new \FOS\UserBundle\Form\Factory\FormFactory($this->get('form.factory'), 'fos_user_profile_form', 'fos_user_profile', array(0 => 'Profile', 1 => 'Default'));
    }

    /**
     * Gets the 'fos_user.profile.form.type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Type\ProfileFormType A FOS\UserBundle\Form\Type\ProfileFormType instance.
     */
    protected function getFosUser_Profile_Form_TypeService()
    {
        return $this->services['fos_user.profile.form.type'] = new \FOS\UserBundle\Form\Type\ProfileFormType('MBH\\Bundle\\UserBundle\\Document\\User');
    }

    /**
     * Gets the 'fos_user.registration.form.factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Factory\FormFactory A FOS\UserBundle\Form\Factory\FormFactory instance.
     */
    protected function getFosUser_Registration_Form_FactoryService()
    {
        return $this->services['fos_user.registration.form.factory'] = new \FOS\UserBundle\Form\Factory\FormFactory($this->get('form.factory'), 'fos_user_registration_form', 'fos_user_registration', array(0 => 'Registration', 1 => 'Default'));
    }

    /**
     * Gets the 'fos_user.registration.form.type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Type\RegistrationFormType A FOS\UserBundle\Form\Type\RegistrationFormType instance.
     */
    protected function getFosUser_Registration_Form_TypeService()
    {
        return $this->services['fos_user.registration.form.type'] = new \FOS\UserBundle\Form\Type\RegistrationFormType('MBH\\Bundle\\UserBundle\\Document\\User');
    }

    /**
     * Gets the 'fos_user.resetting.form.factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Factory\FormFactory A FOS\UserBundle\Form\Factory\FormFactory instance.
     */
    protected function getFosUser_Resetting_Form_FactoryService()
    {
        return $this->services['fos_user.resetting.form.factory'] = new \FOS\UserBundle\Form\Factory\FormFactory($this->get('form.factory'), 'fos_user_resetting_form', 'fos_user_resetting', array(0 => 'ResetPassword', 1 => 'Default'));
    }

    /**
     * Gets the 'fos_user.resetting.form.type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Type\ResettingFormType A FOS\UserBundle\Form\Type\ResettingFormType instance.
     */
    protected function getFosUser_Resetting_Form_TypeService()
    {
        return $this->services['fos_user.resetting.form.type'] = new \FOS\UserBundle\Form\Type\ResettingFormType('MBH\\Bundle\\UserBundle\\Document\\User');
    }

    /**
     * Gets the 'fos_user.security.interactive_login_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\EventListener\LastLoginListener A FOS\UserBundle\EventListener\LastLoginListener instance.
     */
    protected function getFosUser_Security_InteractiveLoginListenerService()
    {
        return $this->services['fos_user.security.interactive_login_listener'] = new \FOS\UserBundle\EventListener\LastLoginListener($this->get('fos_user.user_manager'));
    }

    /**
     * Gets the 'fos_user.security.login_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Security\LoginManager A FOS\UserBundle\Security\LoginManager instance.
     */
    protected function getFosUser_Security_LoginManagerService()
    {
        return $this->services['fos_user.security.login_manager'] = new \FOS\UserBundle\Security\LoginManager($this->get('security.token_storage'), $this->get('security.user_checker.main'), $this->get('security.authentication.session_strategy'), $this);
    }

    /**
     * Gets the 'fos_user.user_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Doctrine\UserManager A FOS\UserBundle\Doctrine\UserManager instance.
     */
    protected function getFosUser_UserManagerService()
    {
        $a = $this->get('fos_user.util.email_canonicalizer');

        return $this->services['fos_user.user_manager'] = new \FOS\UserBundle\Doctrine\UserManager($this->get('security.encoder_factory'), $a, $a, $this->get('fos_user.document_manager'), 'MBH\\Bundle\\UserBundle\\Document\\User');
    }

    /**
     * Gets the 'fos_user.username_form_type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Form\Type\UsernameFormType A FOS\UserBundle\Form\Type\UsernameFormType instance.
     */
    protected function getFosUser_UsernameFormTypeService()
    {
        return $this->services['fos_user.username_form_type'] = new \FOS\UserBundle\Form\Type\UsernameFormType(new \FOS\UserBundle\Form\DataTransformer\UserToUsernameTransformer($this->get('fos_user.user_manager')));
    }

    /**
     * Gets the 'fos_user.util.email_canonicalizer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Util\Canonicalizer A FOS\UserBundle\Util\Canonicalizer instance.
     */
    protected function getFosUser_Util_EmailCanonicalizerService()
    {
        return $this->services['fos_user.util.email_canonicalizer'] = new \FOS\UserBundle\Util\Canonicalizer();
    }

    /**
     * Gets the 'fos_user.util.token_generator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Util\TokenGenerator A FOS\UserBundle\Util\TokenGenerator instance.
     */
    protected function getFosUser_Util_TokenGeneratorService()
    {
        return $this->services['fos_user.util.token_generator'] = new \FOS\UserBundle\Util\TokenGenerator($this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'fos_user.util.user_manipulator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \FOS\UserBundle\Util\UserManipulator A FOS\UserBundle\Util\UserManipulator instance.
     */
    protected function getFosUser_Util_UserManipulatorService()
    {
        return $this->services['fos_user.util.user_manipulator'] = new \FOS\UserBundle\Util\UserManipulator($this->get('fos_user.user_manager'));
    }

    /**
     * Gets the 'fragment.handler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\DependencyInjection\LazyLoadingFragmentHandler A Symfony\Component\HttpKernel\DependencyInjection\LazyLoadingFragmentHandler instance.
     */
    protected function getFragment_HandlerService()
    {
        $this->services['fragment.handler'] = $instance = new \Symfony\Component\HttpKernel\DependencyInjection\LazyLoadingFragmentHandler($this, $this->get('request_stack'), true);

        $instance->addRendererService('inline', 'fragment.renderer.inline');
        $instance->addRendererService('hinclude', 'fragment.renderer.hinclude');
        $instance->addRendererService('hinclude', 'fragment.renderer.hinclude');
        $instance->addRendererService('esi', 'fragment.renderer.esi');
        $instance->addRendererService('ssi', 'fragment.renderer.ssi');

        return $instance;
    }

    /**
     * Gets the 'fragment.listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\FragmentListener A Symfony\Component\HttpKernel\EventListener\FragmentListener instance.
     */
    protected function getFragment_ListenerService()
    {
        return $this->services['fragment.listener'] = new \Symfony\Component\HttpKernel\EventListener\FragmentListener($this->get('uri_signer'), '/_fragment');
    }

    /**
     * Gets the 'fragment.renderer.esi' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer A Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer instance.
     */
    protected function getFragment_Renderer_EsiService()
    {
        $this->services['fragment.renderer.esi'] = $instance = new \Symfony\Component\HttpKernel\Fragment\EsiFragmentRenderer(NULL, $this->get('fragment.renderer.inline'), $this->get('uri_signer'));

        $instance->setFragmentPath('/_fragment');

        return $instance;
    }

    /**
     * Gets the 'fragment.renderer.hinclude' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\Fragment\HIncludeFragmentRenderer A Symfony\Component\HttpKernel\Fragment\HIncludeFragmentRenderer instance.
     */
    protected function getFragment_Renderer_HincludeService()
    {
        $this->services['fragment.renderer.hinclude'] = $instance = new \Symfony\Component\HttpKernel\Fragment\HIncludeFragmentRenderer($this->get('twig'), $this->get('uri_signer'), NULL);

        $instance->setFragmentPath('/_fragment');

        return $instance;
    }

    /**
     * Gets the 'fragment.renderer.inline' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer A Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer instance.
     */
    protected function getFragment_Renderer_InlineService()
    {
        $this->services['fragment.renderer.inline'] = $instance = new \Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer($this->get('http_kernel'), $this->get('debug.event_dispatcher'));

        $instance->setFragmentPath('/_fragment');

        return $instance;
    }

    /**
     * Gets the 'fragment.renderer.ssi' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\Fragment\SsiFragmentRenderer A Symfony\Component\HttpKernel\Fragment\SsiFragmentRenderer instance.
     */
    protected function getFragment_Renderer_SsiService()
    {
        $this->services['fragment.renderer.ssi'] = $instance = new \Symfony\Component\HttpKernel\Fragment\SsiFragmentRenderer(NULL, $this->get('fragment.renderer.inline'), $this->get('uri_signer'));

        $instance->setFragmentPath('/_fragment');

        return $instance;
    }

    /**
     * Gets the 'gedmo.listener.translatable' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Gedmo\Translatable\TranslatableListener A Gedmo\Translatable\TranslatableListener instance.
     */
    protected function getGedmo_Listener_TranslatableService()
    {
        $this->services['gedmo.listener.translatable'] = $instance = new \Gedmo\Translatable\TranslatableListener();

        $instance->setAnnotationReader($this->get('annotation_reader'));
        $instance->setTranslationFallback(true);

        return $instance;
    }

    /**
     * Gets the 'gravatar.api' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Ornicar\GravatarBundle\GravatarApi A Ornicar\GravatarBundle\GravatarApi instance.
     */
    protected function getGravatar_ApiService()
    {
        return $this->services['gravatar.api'] = new \Ornicar\GravatarBundle\GravatarApi(array('rating' => 'g', 'size' => 160, 'default' => 'mm', 'secure' => false));
    }

    /**
     * Gets the 'guzzle.client' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Guzzle\Service\Client A Guzzle\Service\Client instance.
     */
    protected function getGuzzle_ClientService()
    {
        $this->services['guzzle.client'] = $instance = new \Guzzle\Service\Client();

        $instance->addSubscriber($this->get('misd_guzzle.log.array'));
        $instance->addSubscriber($this->get('misd_guzzle.listener.request_listener'));
        $instance->addSubscriber($this->get('misd_guzzle.listener.command_listener'));
        $instance->addSubscriber($this->get('misd_guzzle.log.monolog'));

        return $instance;
    }

    /**
     * Gets the 'guzzle.service_builder' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Guzzle\Service\Builder\ServiceBuilder A Guzzle\Service\Builder\ServiceBuilder instance.
     */
    protected function getGuzzle_ServiceBuilderService()
    {
        $this->services['guzzle.service_builder'] = $instance = \Guzzle\Service\Builder\ServiceBuilder::factory(($this->targetDirs[2].'/config/webservices.json'));

        $instance->addGlobalPlugin($this->get('misd_guzzle.log.array'));
        $instance->addGlobalPlugin($this->get('misd_guzzle.listener.request_listener'));
        $instance->addGlobalPlugin($this->get('misd_guzzle.listener.command_listener'));
        $instance->addGlobalPlugin($this->get('misd_guzzle.log.monolog'));

        return $instance;
    }

    /**
     * Gets the 'http_kernel' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\DependencyInjection\ContainerAwareHttpKernel A Symfony\Component\HttpKernel\DependencyInjection\ContainerAwareHttpKernel instance.
     */
    protected function getHttpKernelService()
    {
        return $this->services['http_kernel'] = new \Symfony\Component\HttpKernel\DependencyInjection\ContainerAwareHttpKernel($this->get('debug.event_dispatcher'), $this, $this->get('debug.controller_resolver'), $this->get('request_stack'), false);
    }

    /**
     * Gets the 'jms_aop.interceptor_loader' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \JMS\AopBundle\Aop\InterceptorLoader A JMS\AopBundle\Aop\InterceptorLoader instance.
     */
    protected function getJmsAop_InterceptorLoaderService()
    {
        return $this->services['jms_aop.interceptor_loader'] = new \JMS\AopBundle\Aop\InterceptorLoader($this, array());
    }

    /**
     * Gets the 'jms_aop.pointcut_container' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \JMS\AopBundle\Aop\PointcutContainer A JMS\AopBundle\Aop\PointcutContainer instance.
     */
    protected function getJmsAop_PointcutContainerService()
    {
        return $this->services['jms_aop.pointcut_container'] = new \JMS\AopBundle\Aop\PointcutContainer(array());
    }

    /**
     * Gets the 'jms_di_extra.metadata.converter' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \JMS\DiExtraBundle\Metadata\MetadataConverter A JMS\DiExtraBundle\Metadata\MetadataConverter instance.
     */
    protected function getJmsDiExtra_Metadata_ConverterService()
    {
        return $this->services['jms_di_extra.metadata.converter'] = new \JMS\DiExtraBundle\Metadata\MetadataConverter();
    }

    /**
     * Gets the 'jms_di_extra.metadata.metadata_factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Metadata\MetadataFactory A Metadata\MetadataFactory instance.
     */
    protected function getJmsDiExtra_Metadata_MetadataFactoryService()
    {
        $this->services['jms_di_extra.metadata.metadata_factory'] = $instance = new \Metadata\MetadataFactory(new \Metadata\Driver\LazyLoadingDriver($this, 'jms_di_extra.metadata_driver'), 'Metadata\\ClassHierarchyMetadata', true);

        $instance->setCache(new \Metadata\Cache\FileCache((__DIR__.'/jms_diextra/metadata')));

        return $instance;
    }

    /**
     * Gets the 'jms_di_extra.metadata_driver' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \JMS\DiExtraBundle\Metadata\Driver\AnnotationDriver A JMS\DiExtraBundle\Metadata\Driver\AnnotationDriver instance.
     */
    protected function getJmsDiExtra_MetadataDriverService()
    {
        return $this->services['jms_di_extra.metadata_driver'] = new \JMS\DiExtraBundle\Metadata\Driver\AnnotationDriver($this->get('annotation_reader'));
    }

    /**
     * Gets the 'jms_translation.config_factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \JMS\TranslationBundle\Translation\ConfigFactory A JMS\TranslationBundle\Translation\ConfigFactory instance.
     */
    protected function getJmsTranslation_ConfigFactoryService()
    {
        $a = new \JMS\TranslationBundle\Translation\ConfigBuilder();
        $a->setTranslationsDir(($this->targetDirs[2].'/Resources/translations'));
        $a->setScanDirs(array(0 => $this->targetDirs[2], 1 => ($this->targetDirs[2].'/../src')));
        $a->setIgnoredDomains(array());
        $a->setDomains(array());
        $a->setEnabledExtractors(array());
        $a->setExcludedDirs(array(0 => ($this->targetDirs[2].'/../var/cache'), 1 => ($this->targetDirs[2].'/../var/logs')));
        $a->setExcludedNames(array(0 => '*TestCase.php', 1 => '*Test.php'));
        $a->setKeepOldTranslations(false);
        $a->setLoadResources(array());

        return $this->services['jms_translation.config_factory'] = new \JMS\TranslationBundle\Translation\ConfigFactory(array('app' => $a));
    }

    /**
     * Gets the 'jms_translation.loader_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \JMS\TranslationBundle\Translation\LoaderManager A JMS\TranslationBundle\Translation\LoaderManager instance.
     */
    protected function getJmsTranslation_LoaderManagerService()
    {
        return $this->services['jms_translation.loader_manager'] = new \JMS\TranslationBundle\Translation\LoaderManager(array('php' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.php')), 'yml' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.yml')), 'xlf' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.xliff')), 'po' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.po')), 'mo' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.mo')), 'ts' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.qt')), 'csv' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.csv')), 'res' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.res')), 'dat' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.dat')), 'ini' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.ini')), 'json' => new \JMS\TranslationBundle\Translation\Loader\SymfonyLoaderAdapter($this->get('translation.loader.json')), 'xliff' => new \JMS\TranslationBundle\Translation\Loader\XliffLoader()));
    }

    /**
     * Gets the 'jms_translation.twig_extension' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \JMS\TranslationBundle\Twig\TranslationExtension A JMS\TranslationBundle\Twig\TranslationExtension instance.
     */
    protected function getJmsTranslation_TwigExtensionService()
    {
        return $this->services['jms_translation.twig_extension'] = new \JMS\TranslationBundle\Twig\TranslationExtension($this->get('translator'), true);
    }

    /**
     * Gets the 'jms_translation.updater' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \JMS\TranslationBundle\Translation\Updater A JMS\TranslationBundle\Translation\Updater instance.
     */
    protected function getJmsTranslation_UpdaterService()
    {
        $a = $this->get('logger');
        $b = $this->get('twig');

        $c = new \Doctrine\Common\Annotations\DocParser();
        $c->setImports(array('desc' => 'JMS\\TranslationBundle\\Annotation\\Desc', 'meaning' => 'JMS\\TranslationBundle\\Annotation\\Meaning', 'ignore' => 'JMS\\TranslationBundle\\Annotation\\Ignore'));
        $c->setIgnoreNotImportedAnnotations(true);

        $d = new \JMS\TranslationBundle\Translation\Dumper\XliffDumper();
        $d->setSourceLanguage('en');

        return $this->services['jms_translation.updater'] = new \JMS\TranslationBundle\Translation\Updater($this->get('jms_translation.loader_manager'), new \JMS\TranslationBundle\Translation\ExtractorManager(new \JMS\TranslationBundle\Translation\Extractor\FileExtractor($b, $a, array(0 => new \JMS\TranslationBundle\Translation\Extractor\File\DefaultPhpFileExtractor($c), 1 => new \JMS\TranslationBundle\Translation\Extractor\File\FormExtractor($c), 2 => new \JMS\TranslationBundle\Translation\Extractor\File\TranslationContainerExtractor(), 3 => new \JMS\TranslationBundle\Translation\Extractor\File\TwigFileExtractor($b), 4 => new \JMS\TranslationBundle\Translation\Extractor\File\ValidationExtractor($this->get('validator')), 5 => new \JMS\TranslationBundle\Translation\Extractor\File\AuthenticationMessagesExtractor($c))), $a, array()), $a, new \JMS\TranslationBundle\Translation\FileWriter(array('php' => new \JMS\TranslationBundle\Translation\Dumper\PhpDumper(), 'xlf' => new \JMS\TranslationBundle\Translation\Dumper\SymfonyDumperAdapter($this->get('translation.dumper.xliff'), 'xlf'), 'po' => new \JMS\TranslationBundle\Translation\Dumper\SymfonyDumperAdapter($this->get('translation.dumper.po'), 'po'), 'mo' => new \JMS\TranslationBundle\Translation\Dumper\SymfonyDumperAdapter($this->get('translation.dumper.mo'), 'mo'), 'yml' => new \JMS\TranslationBundle\Translation\Dumper\YamlDumper(), 'ts' => new \JMS\TranslationBundle\Translation\Dumper\SymfonyDumperAdapter($this->get('translation.dumper.qt'), 'ts'), 'csv' => new \JMS\TranslationBundle\Translation\Dumper\SymfonyDumperAdapter($this->get('translation.dumper.csv'), 'csv'), 'ini' => new \JMS\TranslationBundle\Translation\Dumper\SymfonyDumperAdapter($this->get('translation.dumper.ini'), 'ini'), 'json' => new \JMS\TranslationBundle\Translation\Dumper\SymfonyDumperAdapter($this->get('translation.dumper.json'), 'json'), 'res' => new \JMS\TranslationBundle\Translation\Dumper\SymfonyDumperAdapter($this->get('translation.dumper.res'), 'res'), 'xliff' => $d)));
    }

    /**
     * Gets the 'kernel' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @throws RuntimeException always since this service is expected to be injected dynamically
     */
    protected function getKernelService()
    {
        throw new RuntimeException('You have requested a synthetic service ("kernel"). The DIC does not know how to construct this service.');
    }

    /**
     * Gets the 'kernel.class_cache.cache_warmer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\CacheWarmer\ClassCacheCacheWarmer A Symfony\Bundle\FrameworkBundle\CacheWarmer\ClassCacheCacheWarmer instance.
     */
    protected function getKernel_ClassCache_CacheWarmerService()
    {
        return $this->services['kernel.class_cache.cache_warmer'] = new \Symfony\Bundle\FrameworkBundle\CacheWarmer\ClassCacheCacheWarmer();
    }

    /**
     * Gets the 'knp_menu.factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Knp\Menu\MenuFactory A Knp\Menu\MenuFactory instance.
     */
    protected function getKnpMenu_FactoryService()
    {
        $this->services['knp_menu.factory'] = $instance = new \Knp\Menu\MenuFactory();

        $instance->addExtension(new \Knp\Menu\Integration\Symfony\RoutingExtension($this->get('router')), 0);

        return $instance;
    }

    /**
     * Gets the 'knp_menu.listener.voters' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Knp\Bundle\MenuBundle\EventListener\VoterInitializerListener A Knp\Bundle\MenuBundle\EventListener\VoterInitializerListener instance.
     */
    protected function getKnpMenu_Listener_VotersService()
    {
        $this->services['knp_menu.listener.voters'] = $instance = new \Knp\Bundle\MenuBundle\EventListener\VoterInitializerListener();

        $instance->addVoter($this->get('knp_menu.voter.router'));

        return $instance;
    }

    /**
     * Gets the 'knp_menu.matcher' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Knp\Menu\Matcher\Matcher A Knp\Menu\Matcher\Matcher instance.
     */
    protected function getKnpMenu_MatcherService()
    {
        $this->services['knp_menu.matcher'] = $instance = new \Knp\Menu\Matcher\Matcher();

        $instance->addVoter($this->get('knp_menu.voter.router'));

        return $instance;
    }

    /**
     * Gets the 'knp_menu.menu_provider' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Knp\Menu\Provider\ChainProvider A Knp\Menu\Provider\ChainProvider instance.
     */
    protected function getKnpMenu_MenuProviderService()
    {
        return $this->services['knp_menu.menu_provider'] = new \Knp\Menu\Provider\ChainProvider(array(0 => new \Knp\Bundle\MenuBundle\Provider\ContainerAwareProvider($this, array()), 1 => new \Knp\Bundle\MenuBundle\Provider\BuilderAliasProvider($this->get('kernel'), $this, $this->get('knp_menu.factory'))));
    }

    /**
     * Gets the 'knp_menu.renderer.list' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Knp\Menu\Renderer\ListRenderer A Knp\Menu\Renderer\ListRenderer instance.
     */
    protected function getKnpMenu_Renderer_ListService()
    {
        return $this->services['knp_menu.renderer.list'] = new \Knp\Menu\Renderer\ListRenderer($this->get('knp_menu.matcher'), array(), 'UTF-8');
    }

    /**
     * Gets the 'knp_menu.renderer.twig' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Knp\Menu\Renderer\TwigRenderer A Knp\Menu\Renderer\TwigRenderer instance.
     */
    protected function getKnpMenu_Renderer_TwigService()
    {
        return $this->services['knp_menu.renderer.twig'] = new \Knp\Menu\Renderer\TwigRenderer($this->get('twig'), 'MBHBaseBundle:Menu:menu.html.twig', $this->get('knp_menu.matcher'), array('currentClass' => 'active'));
    }

    /**
     * Gets the 'knp_menu.renderer_provider' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Knp\Bundle\MenuBundle\Renderer\ContainerAwareProvider A Knp\Bundle\MenuBundle\Renderer\ContainerAwareProvider instance.
     */
    protected function getKnpMenu_RendererProviderService()
    {
        return $this->services['knp_menu.renderer_provider'] = new \Knp\Bundle\MenuBundle\Renderer\ContainerAwareProvider($this, 'twig', array('list' => 'knp_menu.renderer.list', 'twig' => 'knp_menu.renderer.twig'));
    }

    /**
     * Gets the 'knp_menu.voter.router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Knp\Menu\Matcher\Voter\RouteVoter A Knp\Menu\Matcher\Voter\RouteVoter instance.
     */
    protected function getKnpMenu_Voter_RouterService()
    {
        return $this->services['knp_menu.voter.router'] = new \Knp\Menu\Matcher\Voter\RouteVoter();
    }

    /**
     * Gets the 'knp_snappy.image' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator A Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator instance.
     */
    protected function getKnpSnappy_ImageService()
    {
        return $this->services['knp_snappy.image'] = new \Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator(new \Knp\Snappy\Image('/usr/local/bin/h', array(), array()), $this->get('monolog.logger.snappy', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'knp_snappy.pdf' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator A Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator instance.
     */
    protected function getKnpSnappy_PdfService()
    {
        return $this->services['knp_snappy.pdf'] = new \Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator(new \Knp\Snappy\Pdf('/usr/local/bin/wkhtmltopdf', array(), array()), $this->get('monolog.logger.snappy', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'liip_imagine' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Imagine\Gd\Imagine A Imagine\Gd\Imagine instance.
     */
    protected function getLiipImagineService()
    {
        $this->services['liip_imagine'] = $instance = new \Imagine\Gd\Imagine();

        $instance->setMetadataReader(new \Imagine\Image\Metadata\ExifMetadataReader());

        return $instance;
    }

    /**
     * Gets the 'liip_imagine.binary.loader.default' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Binary\Loader\FileSystemLoader A Liip\ImagineBundle\Binary\Loader\FileSystemLoader instance.
     */
    protected function getLiipImagine_Binary_Loader_DefaultService()
    {
        return $this->services['liip_imagine.binary.loader.default'] = new \Liip\ImagineBundle\Binary\Loader\FileSystemLoader($this->get('liip_imagine.mime_type_guesser'), $this->get('liip_imagine.extension_guesser'), ($this->targetDirs[2].'/../web'));
    }

    /**
     * Gets the 'liip_imagine.binary.loader.protected' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Binary\Loader\FileSystemLoader A Liip\ImagineBundle\Binary\Loader\FileSystemLoader instance.
     */
    protected function getLiipImagine_Binary_Loader_ProtectedService()
    {
        return $this->services['liip_imagine.binary.loader.protected'] = new \Liip\ImagineBundle\Binary\Loader\FileSystemLoader($this->get('liip_imagine.mime_type_guesser'), $this->get('liip_imagine.extension_guesser'), ($this->targetDirs[2].'/../protectedUpload'));
    }

    /**
     * Gets the 'liip_imagine.binary.loader.prototype.filesystem' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Binary\Loader\FileSystemLoader A Liip\ImagineBundle\Binary\Loader\FileSystemLoader instance.
     */
    protected function getLiipImagine_Binary_Loader_Prototype_FilesystemService()
    {
        return $this->services['liip_imagine.binary.loader.prototype.filesystem'] = new \Liip\ImagineBundle\Binary\Loader\FileSystemLoader($this->get('liip_imagine.mime_type_guesser'), $this->get('liip_imagine.extension_guesser'), '');
    }

    /**
     * Gets the 'liip_imagine.binary.loader.prototype.stream' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Binary\Loader\StreamLoader A Liip\ImagineBundle\Binary\Loader\StreamLoader instance.
     */
    protected function getLiipImagine_Binary_Loader_Prototype_StreamService()
    {
        return $this->services['liip_imagine.binary.loader.prototype.stream'] = new \Liip\ImagineBundle\Binary\Loader\StreamLoader('', '');
    }

    /**
     * Gets the 'liip_imagine.binary.mime_type_guesser' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser A Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser instance.
     */
    protected function getLiipImagine_Binary_MimeTypeGuesserService()
    {
        return $this->services['liip_imagine.binary.mime_type_guesser'] = new \Liip\ImagineBundle\Binary\SimpleMimeTypeGuesser($this->get('liip_imagine.mime_type_guesser'));
    }

    /**
     * Gets the 'liip_imagine.cache.manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Cache\CacheManager A Liip\ImagineBundle\Imagine\Cache\CacheManager instance.
     */
    protected function getLiipImagine_Cache_ManagerService()
    {
        $this->services['liip_imagine.cache.manager'] = $instance = new \Liip\ImagineBundle\Imagine\Cache\CacheManager($this->get('liip_imagine.filter.configuration'), $this->get('router'), $this->get('liip_imagine.cache.signer'), $this->get('debug.event_dispatcher'), 'default');

        $instance->addResolver('default', $this->get('liip_imagine.cache.resolver.default'));
        $instance->addResolver('no_cache', $this->get('liip_imagine.cache.resolver.no_cache_web_path'));

        return $instance;
    }

    /**
     * Gets the 'liip_imagine.cache.resolver.default' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver A Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver instance.
     */
    protected function getLiipImagine_Cache_Resolver_DefaultService()
    {
        return $this->services['liip_imagine.cache.resolver.default'] = new \Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver($this->get('filesystem'), $this->get('router.request_context'), ($this->targetDirs[2].'/../web'), 'media/cache');
    }

    /**
     * Gets the 'liip_imagine.cache.resolver.no_cache_web_path' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver A Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver instance.
     */
    protected function getLiipImagine_Cache_Resolver_NoCacheWebPathService()
    {
        return $this->services['liip_imagine.cache.resolver.no_cache_web_path'] = new \Liip\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver($this->get('router.request_context'));
    }

    /**
     * Gets the 'liip_imagine.cache.signer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Cache\Signer A Liip\ImagineBundle\Imagine\Cache\Signer instance.
     */
    protected function getLiipImagine_Cache_SignerService()
    {
        return $this->services['liip_imagine.cache.signer'] = new \Liip\ImagineBundle\Imagine\Cache\Signer('mySyperSecretKeyForSymfony');
    }

    /**
     * Gets the 'liip_imagine.controller' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Controller\ImagineController A Liip\ImagineBundle\Controller\ImagineController instance.
     */
    protected function getLiipImagine_ControllerService()
    {
        return $this->services['liip_imagine.controller'] = new \Liip\ImagineBundle\Controller\ImagineController($this->get('liip_imagine.data.manager'), $this->get('liip_imagine.filter.manager'), $this->get('liip_imagine.cache.manager'), $this->get('liip_imagine.cache.signer'), $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'liip_imagine.data.manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Data\DataManager A Liip\ImagineBundle\Imagine\Data\DataManager instance.
     */
    protected function getLiipImagine_Data_ManagerService()
    {
        $this->services['liip_imagine.data.manager'] = $instance = new \Liip\ImagineBundle\Imagine\Data\DataManager($this->get('liip_imagine.binary.mime_type_guesser'), $this->get('liip_imagine.extension_guesser'), $this->get('liip_imagine.filter.configuration'), 'default', NULL);

        $instance->addLoader('protected', $this->get('liip_imagine.binary.loader.protected'));
        $instance->addLoader('default', $this->get('liip_imagine.binary.loader.default'));

        return $instance;
    }

    /**
     * Gets the 'liip_imagine.extension_guesser' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface A Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface instance.
     */
    protected function getLiipImagine_ExtensionGuesserService()
    {
        return $this->services['liip_imagine.extension_guesser'] = \Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser::getInstance();
    }

    /**
     * Gets the 'liip_imagine.filter.configuration' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\FilterConfiguration A Liip\ImagineBundle\Imagine\Filter\FilterConfiguration instance.
     */
    protected function getLiipImagine_Filter_ConfigurationService()
    {
        return $this->services['liip_imagine.filter.configuration'] = new \Liip\ImagineBundle\Imagine\Filter\FilterConfiguration(array('cache' => array('quality' => 100, 'jpeg_quality' => NULL, 'png_compression_level' => NULL, 'png_compression_filter' => NULL, 'format' => NULL, 'animated' => false, 'cache' => NULL, 'data_loader' => NULL, 'default_image' => NULL, 'filters' => array(), 'post_processors' => array()), 'thumb_100x100' => array('quality' => 100, 'filters' => array('thumbnail' => array('size' => array(0 => 100, 1 => 100), 'mode' => 'outbound', 'allow_upscale' => true)), 'jpeg_quality' => NULL, 'png_compression_level' => NULL, 'png_compression_filter' => NULL, 'format' => NULL, 'animated' => false, 'cache' => NULL, 'data_loader' => NULL, 'default_image' => NULL, 'post_processors' => array()), 'thumb_130x110' => array('quality' => 100, 'filters' => array('thumbnail' => array('size' => array(0 => 130, 1 => 110), 'mode' => 'outbound', 'allow_upscale' => true)), 'jpeg_quality' => NULL, 'png_compression_level' => NULL, 'png_compression_filter' => NULL, 'format' => NULL, 'animated' => false, 'cache' => NULL, 'data_loader' => NULL, 'default_image' => NULL, 'post_processors' => array()), 'thumb_95x80' => array('quality' => 100, 'filters' => array('thumbnail' => array('size' => array(0 => 95, 1 => 80), 'mode' => 'outbound', 'allow_upscale' => true)), 'jpeg_quality' => NULL, 'png_compression_level' => NULL, 'png_compression_filter' => NULL, 'format' => NULL, 'animated' => false, 'cache' => NULL, 'data_loader' => NULL, 'default_image' => NULL, 'post_processors' => array()), 'stamp' => array('data_loader' => 'protected', 'quality' => 100, 'filters' => array('thumbnail' => array('size' => array(0 => 10, 1 => 10), 'mode' => 'outbound', 'allow_upscale' => true)), 'jpeg_quality' => NULL, 'png_compression_level' => NULL, 'png_compression_filter' => NULL, 'format' => NULL, 'animated' => false, 'cache' => NULL, 'default_image' => NULL, 'post_processors' => array()), 'scaler' => array('quality' => 100, 'filters' => array('relative_resize' => array('scale' => 0.5, 'allow_upscale' => true)), 'jpeg_quality' => NULL, 'png_compression_level' => NULL, 'png_compression_filter' => NULL, 'format' => NULL, 'animated' => false, 'cache' => NULL, 'data_loader' => NULL, 'default_image' => NULL, 'post_processors' => array())));
    }

    /**
     * Gets the 'liip_imagine.filter.loader.auto_rotate' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\AutoRotateFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\AutoRotateFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_AutoRotateService()
    {
        return $this->services['liip_imagine.filter.loader.auto_rotate'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\AutoRotateFilterLoader();
    }

    /**
     * Gets the 'liip_imagine.filter.loader.background' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\BackgroundFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\BackgroundFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_BackgroundService()
    {
        return $this->services['liip_imagine.filter.loader.background'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\BackgroundFilterLoader($this->get('liip_imagine'));
    }

    /**
     * Gets the 'liip_imagine.filter.loader.crop' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\CropFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\CropFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_CropService()
    {
        return $this->services['liip_imagine.filter.loader.crop'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\CropFilterLoader();
    }

    /**
     * Gets the 'liip_imagine.filter.loader.interlace' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\InterlaceFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\InterlaceFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_InterlaceService()
    {
        return $this->services['liip_imagine.filter.loader.interlace'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\InterlaceFilterLoader();
    }

    /**
     * Gets the 'liip_imagine.filter.loader.paste' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\PasteFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\PasteFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_PasteService()
    {
        return $this->services['liip_imagine.filter.loader.paste'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\PasteFilterLoader($this->get('liip_imagine'), $this->targetDirs[2]);
    }

    /**
     * Gets the 'liip_imagine.filter.loader.relative_resize' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\RelativeResizeFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\RelativeResizeFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_RelativeResizeService()
    {
        return $this->services['liip_imagine.filter.loader.relative_resize'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\RelativeResizeFilterLoader();
    }

    /**
     * Gets the 'liip_imagine.filter.loader.resize' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\ResizeFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\ResizeFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_ResizeService()
    {
        return $this->services['liip_imagine.filter.loader.resize'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\ResizeFilterLoader();
    }

    /**
     * Gets the 'liip_imagine.filter.loader.rotate' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\RotateFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\RotateFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_RotateService()
    {
        return $this->services['liip_imagine.filter.loader.rotate'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\RotateFilterLoader();
    }

    /**
     * Gets the 'liip_imagine.filter.loader.strip' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\StripFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\StripFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_StripService()
    {
        return $this->services['liip_imagine.filter.loader.strip'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\StripFilterLoader();
    }

    /**
     * Gets the 'liip_imagine.filter.loader.thumbnail' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_ThumbnailService()
    {
        return $this->services['liip_imagine.filter.loader.thumbnail'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\ThumbnailFilterLoader();
    }

    /**
     * Gets the 'liip_imagine.filter.loader.upscale' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\UpscaleFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\UpscaleFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_UpscaleService()
    {
        return $this->services['liip_imagine.filter.loader.upscale'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\UpscaleFilterLoader();
    }

    /**
     * Gets the 'liip_imagine.filter.loader.watermark' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\Loader\WatermarkFilterLoader A Liip\ImagineBundle\Imagine\Filter\Loader\WatermarkFilterLoader instance.
     */
    protected function getLiipImagine_Filter_Loader_WatermarkService()
    {
        return $this->services['liip_imagine.filter.loader.watermark'] = new \Liip\ImagineBundle\Imagine\Filter\Loader\WatermarkFilterLoader($this->get('liip_imagine'), $this->targetDirs[2]);
    }

    /**
     * Gets the 'liip_imagine.filter.manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\FilterManager A Liip\ImagineBundle\Imagine\Filter\FilterManager instance.
     */
    protected function getLiipImagine_Filter_ManagerService()
    {
        $this->services['liip_imagine.filter.manager'] = $instance = new \Liip\ImagineBundle\Imagine\Filter\FilterManager($this->get('liip_imagine.filter.configuration'), $this->get('liip_imagine'), $this->get('liip_imagine.binary.mime_type_guesser'));

        $instance->addLoader('relative_resize', $this->get('liip_imagine.filter.loader.relative_resize'));
        $instance->addLoader('resize', $this->get('liip_imagine.filter.loader.resize'));
        $instance->addLoader('thumbnail', $this->get('liip_imagine.filter.loader.thumbnail'));
        $instance->addLoader('crop', $this->get('liip_imagine.filter.loader.crop'));
        $instance->addLoader('paste', $this->get('liip_imagine.filter.loader.paste'));
        $instance->addLoader('watermark', $this->get('liip_imagine.filter.loader.watermark'));
        $instance->addLoader('background', $this->get('liip_imagine.filter.loader.background'));
        $instance->addLoader('strip', $this->get('liip_imagine.filter.loader.strip'));
        $instance->addLoader('upscale', $this->get('liip_imagine.filter.loader.upscale'));
        $instance->addLoader('auto_rotate', $this->get('liip_imagine.filter.loader.auto_rotate'));
        $instance->addLoader('rotate', $this->get('liip_imagine.filter.loader.rotate'));
        $instance->addLoader('interlace', $this->get('liip_imagine.filter.loader.interlace'));
        $instance->addPostProcessor('jpegoptim', $this->get('liip_imagine.filter.post_processor.jpegoptim'));

        return $instance;
    }

    /**
     * Gets the 'liip_imagine.filter.post_processor.jpegoptim' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Imagine\Filter\PostProcessor\JpegOptimPostProcessor A Liip\ImagineBundle\Imagine\Filter\PostProcessor\JpegOptimPostProcessor instance.
     */
    protected function getLiipImagine_Filter_PostProcessor_JpegoptimService()
    {
        return $this->services['liip_imagine.filter.post_processor.jpegoptim'] = new \Liip\ImagineBundle\Imagine\Filter\PostProcessor\JpegOptimPostProcessor('/usr/bin/jpegoptim');
    }

    /**
     * Gets the 'liip_imagine.form.type.image' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Form\Type\ImageType A Liip\ImagineBundle\Form\Type\ImageType instance.
     */
    protected function getLiipImagine_Form_Type_ImageService()
    {
        return $this->services['liip_imagine.form.type.image'] = new \Liip\ImagineBundle\Form\Type\ImageType();
    }

    /**
     * Gets the 'liip_imagine.mime_type_guesser' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface A Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface instance.
     */
    protected function getLiipImagine_MimeTypeGuesserService()
    {
        return $this->services['liip_imagine.mime_type_guesser'] = \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser::getInstance();
    }

    /**
     * Gets the 'liip_imagine.templating.helper' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liip\ImagineBundle\Templating\Helper\ImagineHelper A Liip\ImagineBundle\Templating\Helper\ImagineHelper instance.
     */
    protected function getLiipImagine_Templating_HelperService()
    {
        return $this->services['liip_imagine.templating.helper'] = new \Liip\ImagineBundle\Templating\Helper\ImagineHelper($this->get('liip_imagine.cache.manager'));
    }

    /**
     * Gets the 'locale_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\LocaleListener A Symfony\Component\HttpKernel\EventListener\LocaleListener instance.
     */
    protected function getLocaleListenerService()
    {
        return $this->services['locale_listener'] = new \Symfony\Component\HttpKernel\EventListener\LocaleListener($this->get('request_stack'), 'ru', $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'logger' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getLoggerService()
    {
        $this->services['logger'] = $instance = new \Symfony\Bridge\Monolog\Logger('app');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'mbh.base_on_controller_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\EventListener\OnController A MBH\Bundle\BaseBundle\EventListener\OnController instance.
     */
    protected function getMbh_BaseOnControllerListenerService()
    {
        return $this->services['mbh.base_on_controller_listener'] = new \MBH\Bundle\BaseBundle\EventListener\OnController($this);
    }

    /**
     * Gets the 'mbh.base_on_request_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\EventListener\OnRequest A MBH\Bundle\BaseBundle\EventListener\OnRequest instance.
     */
    protected function getMbh_BaseOnRequestListenerService()
    {
        return $this->services['mbh.base_on_request_listener'] = new \MBH\Bundle\BaseBundle\EventListener\OnRequest($this);
    }

    /**
     * Gets the 'mbh.cache' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Service\Cache A MBH\Bundle\BaseBundle\Service\Cache instance.
     */
    protected function getMbh_CacheService()
    {
        return $this->services['mbh.cache'] = new \MBH\Bundle\BaseBundle\Service\Cache(array('is_enabled' => false, 'prefix' => 'mbh'), $this->get('memcache.default'));
    }

    /**
     * Gets the 'mbh.calculation' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Services\Calculation A MBH\Bundle\PackageBundle\Services\Calculation instance.
     */
    protected function getMbh_CalculationService()
    {
        return $this->services['mbh.calculation'] = new \MBH\Bundle\PackageBundle\Services\Calculation($this);
    }

    /**
     * Gets the 'mbh.cash' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\CashBundle\Service\Cash A MBH\Bundle\CashBundle\Service\Cash instance.
     */
    protected function getMbh_CashService()
    {
        return $this->services['mbh.cash'] = new \MBH\Bundle\CashBundle\Service\Cash($this);
    }

    /**
     * Gets the 'mbh.cash.1c_exporter' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\CashBundle\Service\OneCExporter A MBH\Bundle\CashBundle\Service\OneCExporter instance.
     */
    protected function getMbh_Cash_1cExporterService()
    {
        return $this->services['mbh.cash.1c_exporter'] = new \MBH\Bundle\CashBundle\Service\OneCExporter($this);
    }

    /**
     * Gets the 'mbh.cash.document.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\CashBundle\EventListener\CashDocumentSubscriber A MBH\Bundle\CashBundle\EventListener\CashDocumentSubscriber instance.
     */
    protected function getMbh_Cash_Document_SubscriberService()
    {
        return $this->services['mbh.cash.document.subscriber'] = new \MBH\Bundle\CashBundle\EventListener\CashDocumentSubscriber($this);
    }

    /**
     * Gets the 'mbh.channelmanager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Services\ChannelManager A MBH\Bundle\ChannelManagerBundle\Services\ChannelManager instance.
     */
    protected function getMbh_ChannelmanagerService()
    {
        return $this->services['mbh.channelmanager'] = new \MBH\Bundle\ChannelManagerBundle\Services\ChannelManager($this);
    }

    /**
     * Gets the 'mbh.channelmanager.booking' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Services\Booking A MBH\Bundle\ChannelManagerBundle\Services\Booking instance.
     */
    protected function getMbh_Channelmanager_BookingService()
    {
        return $this->services['mbh.channelmanager.booking'] = new \MBH\Bundle\ChannelManagerBundle\Services\Booking($this);
    }

    /**
     * Gets the 'mbh.channelmanager.booking_type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Form\BookingType A MBH\Bundle\ChannelManagerBundle\Form\BookingType instance.
     */
    protected function getMbh_Channelmanager_BookingTypeService()
    {
        return $this->services['mbh.channelmanager.booking_type'] = new \MBH\Bundle\ChannelManagerBundle\Form\BookingType($this->get('mbh.currency'));
    }

    /**
     * Gets the 'mbh.channelmanager.configs.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\ChannelManagerBundle\EventListener\ConfigsSubscriber A MBH\Bundle\ChannelManagerBundle\EventListener\ConfigsSubscriber instance.
     */
    protected function getMbh_Channelmanager_Configs_SubscriberService()
    {
        return $this->services['mbh.channelmanager.configs.subscriber'] = new \MBH\Bundle\ChannelManagerBundle\EventListener\ConfigsSubscriber($this);
    }

    /**
     * Gets the 'mbh.channelmanager.logger' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMbh_Channelmanager_LoggerService()
    {
        $this->services['mbh.channelmanager.logger'] = $instance = new \Symfony\Bridge\Monolog\Logger('app');

        $instance->pushHandler($this->get('mbh.channelmanager.logger_handler'));

        return $instance;
    }

    /**
     * Gets the 'mbh.channelmanager.logger_handler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Monolog\Handler\StreamHandler A Monolog\Handler\StreamHandler instance.
     */
    protected function getMbh_Channelmanager_LoggerHandlerService()
    {
        return $this->services['mbh.channelmanager.logger_handler'] = new \Monolog\Handler\StreamHandler(($this->targetDirs[2].'/logs/dev.channelmanager.log'), 200);
    }

    /**
     * Gets the 'mbh.channelmanager.myallocator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Services\MyAllocator A MBH\Bundle\ChannelManagerBundle\Services\MyAllocator instance.
     */
    protected function getMbh_Channelmanager_MyallocatorService()
    {
        return $this->services['mbh.channelmanager.myallocator'] = new \MBH\Bundle\ChannelManagerBundle\Services\MyAllocator($this);
    }

    /**
     * Gets the 'mbh.channelmanager.myallocator_type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Form\MyallocatorType A MBH\Bundle\ChannelManagerBundle\Form\MyallocatorType instance.
     */
    protected function getMbh_Channelmanager_MyallocatorTypeService()
    {
        return $this->services['mbh.channelmanager.myallocator_type'] = new \MBH\Bundle\ChannelManagerBundle\Form\MyallocatorType($this->get('mbh.channelmanager.myallocator'), $this->get('mbh.currency'));
    }

    /**
     * Gets the 'mbh.channelmanager.oktogo' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Services\Oktogo A MBH\Bundle\ChannelManagerBundle\Services\Oktogo instance.
     */
    protected function getMbh_Channelmanager_OktogoService()
    {
        return $this->services['mbh.channelmanager.oktogo'] = new \MBH\Bundle\ChannelManagerBundle\Services\Oktogo($this);
    }

    /**
     * Gets the 'mbh.channelmanager.ostrovok' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Services\Ostrovok A MBH\Bundle\ChannelManagerBundle\Services\Ostrovok instance.
     */
    protected function getMbh_Channelmanager_OstrovokService()
    {
        return $this->services['mbh.channelmanager.ostrovok'] = new \MBH\Bundle\ChannelManagerBundle\Services\Ostrovok($this);
    }

    /**
     * Gets the 'mbh.channelmanager.vashotel' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\ChannelManagerBundle\Services\Vashotel A MBH\Bundle\ChannelManagerBundle\Services\Vashotel instance.
     */
    protected function getMbh_Channelmanager_VashotelService()
    {
        return $this->services['mbh.channelmanager.vashotel'] = new \MBH\Bundle\ChannelManagerBundle\Services\Vashotel($this);
    }

    /**
     * Gets the 'mbh.check_hotel.action_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\EventListener\CheckHotelListener A MBH\Bundle\HotelBundle\EventListener\CheckHotelListener instance.
     */
    protected function getMbh_CheckHotel_ActionListenerService()
    {
        return $this->services['mbh.check_hotel.action_listener'] = new \MBH\Bundle\HotelBundle\EventListener\CheckHotelListener($this);
    }

    /**
     * Gets the 'mbh.currency' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Service\Currency A MBH\Bundle\BaseBundle\Service\Currency instance.
     */
    protected function getMbh_CurrencyService()
    {
        return $this->services['mbh.currency'] = new \MBH\Bundle\BaseBundle\Service\Currency($this);
    }

    /**
     * Gets the 'mbh.event_listener.check_relation_subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\EventListener\CheckDeleteRelationSubscriber A MBH\Bundle\BaseBundle\EventListener\CheckDeleteRelationSubscriber instance.
     */
    protected function getMbh_EventListener_CheckRelationSubscriberService()
    {
        return $this->services['mbh.event_listener.check_relation_subscriber'] = new \MBH\Bundle\BaseBundle\EventListener\CheckDeleteRelationSubscriber();
    }

    /**
     * Gets the 'mbh.event_listener.generate_internationl_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\EventListener\GenerateInternationalListener A MBH\Bundle\BaseBundle\EventListener\GenerateInternationalListener instance.
     */
    protected function getMbh_EventListener_GenerateInternationlListenerService()
    {
        return $this->services['mbh.event_listener.generate_internationl_listener'] = new \MBH\Bundle\BaseBundle\EventListener\GenerateInternationalListener();
    }

    /**
     * Gets the 'mbh.event_listener.hotelable_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\EventListener\HotelableListener A MBH\Bundle\BaseBundle\EventListener\HotelableListener instance.
     */
    protected function getMbh_EventListener_HotelableListenerService()
    {
        return $this->services['mbh.event_listener.hotelable_listener'] = new \MBH\Bundle\BaseBundle\EventListener\HotelableListener($this->get('mbh.hotel.selector'));
    }

    /**
     * Gets the 'mbh.event_listener.versioned_subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\EventListener\VersionedSubscriber A MBH\Bundle\BaseBundle\EventListener\VersionedSubscriber instance.
     */
    protected function getMbh_EventListener_VersionedSubscriberService()
    {
        return $this->services['mbh.event_listener.versioned_subscriber'] = new \MBH\Bundle\BaseBundle\EventListener\VersionedSubscriber($this);
    }

    /**
     * Gets the 'mbh.facility_repository' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\Document\FacilityRepository A MBH\Bundle\HotelBundle\Document\FacilityRepository instance.
     */
    protected function getMbh_FacilityRepositoryService()
    {
        $this->services['mbh.facility_repository'] = $instance = new \MBH\Bundle\HotelBundle\Document\FacilityRepository();

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'mbh.form.bottom_extension' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Form\Extension\BottomMessageTypeExtension A MBH\Bundle\BaseBundle\Form\Extension\BottomMessageTypeExtension instance.
     */
    protected function getMbh_Form_BottomExtensionService()
    {
        return $this->services['mbh.form.bottom_extension'] = new \MBH\Bundle\BaseBundle\Form\Extension\BottomMessageTypeExtension();
    }

    /**
     * Gets the 'mbh.form.fieldset_extension' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Form\Extension\FieldsetExtension A MBH\Bundle\BaseBundle\Form\Extension\FieldsetExtension instance.
     */
    protected function getMbh_Form_FieldsetExtensionService()
    {
        return $this->services['mbh.form.fieldset_extension'] = new \MBH\Bundle\BaseBundle\Form\Extension\FieldsetExtension();
    }

    /**
     * Gets the 'mbh.form.help_extension' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Form\Extension\HelpMessageTypeExtension A MBH\Bundle\BaseBundle\Form\Extension\HelpMessageTypeExtension instance.
     */
    protected function getMbh_Form_HelpExtensionService()
    {
        return $this->services['mbh.form.help_extension'] = new \MBH\Bundle\BaseBundle\Form\Extension\HelpMessageTypeExtension();
    }

    /**
     * Gets the 'mbh.get_set_method_normalizer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer A Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer instance.
     */
    protected function getMbh_GetSetMethodNormalizerService()
    {
        return $this->services['mbh.get_set_method_normalizer'] = new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer();
    }

    /**
     * Gets the 'mbh.helper' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Service\Helper A MBH\Bundle\BaseBundle\Service\Helper instance.
     */
    protected function getMbh_HelperService()
    {
        return $this->services['mbh.helper'] = new \MBH\Bundle\BaseBundle\Service\Helper($this);
    }

    /**
     * Gets the 'mbh.hotel.auto_task_creator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\Service\AutoTaskCreator A MBH\Bundle\HotelBundle\Service\AutoTaskCreator instance.
     */
    protected function getMbh_Hotel_AutoTaskCreatorService()
    {
        return $this->services['mbh.hotel.auto_task_creator'] = new \MBH\Bundle\HotelBundle\Service\AutoTaskCreator($this);
    }

    /**
     * Gets the 'mbh.hotel.console_auto_task_creator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\Service\ConsoleAutoTaskCreator A MBH\Bundle\HotelBundle\Service\ConsoleAutoTaskCreator instance.
     */
    protected function getMbh_Hotel_ConsoleAutoTaskCreatorService()
    {
        return $this->services['mbh.hotel.console_auto_task_creator'] = new \MBH\Bundle\HotelBundle\Service\ConsoleAutoTaskCreator($this);
    }

    /**
     * Gets the 'mbh.hotel.facility.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\EventListener\FacilitySubscriber A MBH\Bundle\HotelBundle\EventListener\FacilitySubscriber instance.
     */
    protected function getMbh_Hotel_Facility_SubscriberService()
    {
        $this->services['mbh.hotel.facility.subscriber'] = $instance = new \MBH\Bundle\HotelBundle\EventListener\FacilitySubscriber();

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'mbh.hotel.hotel_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\Service\HotelManager A MBH\Bundle\HotelBundle\Service\HotelManager instance.
     */
    protected function getMbh_Hotel_HotelManagerService()
    {
        return $this->services['mbh.hotel.hotel_manager'] = new \MBH\Bundle\HotelBundle\Service\HotelManager($this);
    }

    /**
     * Gets the 'mbh.hotel.room.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\EventListener\RoomSubscriber A MBH\Bundle\HotelBundle\EventListener\RoomSubscriber instance.
     */
    protected function getMbh_Hotel_Room_SubscriberService()
    {
        return $this->services['mbh.hotel.room.subscriber'] = new \MBH\Bundle\HotelBundle\EventListener\RoomSubscriber($this);
    }

    /**
     * Gets the 'mbh.hotel.room_type.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\EventListener\RoomTypeSubscriber A MBH\Bundle\HotelBundle\EventListener\RoomTypeSubscriber instance.
     */
    protected function getMbh_Hotel_RoomType_SubscriberService()
    {
        return $this->services['mbh.hotel.room_type.subscriber'] = new \MBH\Bundle\HotelBundle\EventListener\RoomTypeSubscriber();
    }

    /**
     * Gets the 'mbh.hotel.room_type_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\Service\RoomTypeManager A MBH\Bundle\HotelBundle\Service\RoomTypeManager instance.
     */
    protected function getMbh_Hotel_RoomTypeManagerService()
    {
        return $this->services['mbh.hotel.room_type_manager'] = new \MBH\Bundle\HotelBundle\Service\RoomTypeManager($this);
    }

    /**
     * Gets the 'mbh.hotel.selector' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Service\HotelSelector A MBH\Bundle\BaseBundle\Service\HotelSelector instance.
     */
    protected function getMbh_Hotel_SelectorService()
    {
        return $this->services['mbh.hotel.selector'] = new \MBH\Bundle\BaseBundle\Service\HotelSelector($this);
    }

    /**
     * Gets the 'mbh.hotel.task.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\EventListener\TaskSubscriber A MBH\Bundle\HotelBundle\EventListener\TaskSubscriber instance.
     */
    protected function getMbh_Hotel_Task_SubscriberService()
    {
        return $this->services['mbh.hotel.task.subscriber'] = new \MBH\Bundle\HotelBundle\EventListener\TaskSubscriber($this);
    }

    /**
     * Gets the 'mbh.hotel.task_repository' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\HotelBundle\Document\TaskRepository A MBH\Bundle\HotelBundle\Document\TaskRepository instance.
     */
    protected function getMbh_Hotel_TaskRepositoryService()
    {
        $this->services['mbh.hotel.task_repository'] = $instance = $this->get('doctrine_mongodb.odm.default_document_manager')->getRepository('MBH\\Bundle\\HotelBundle\\Document\\Task');

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'mbh.mailer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Service\Messenger\Mailer A MBH\Bundle\BaseBundle\Service\Messenger\Mailer instance.
     */
    protected function getMbh_MailerService()
    {
        return $this->services['mbh.mailer'] = new \MBH\Bundle\BaseBundle\Service\Messenger\Mailer($this);
    }

    /**
     * Gets the 'mbh.mbhs' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\ClientBundle\Service\Mbhs A MBH\Bundle\ClientBundle\Service\Mbhs instance.
     */
    protected function getMbh_MbhsService()
    {
        return $this->services['mbh.mbhs'] = new \MBH\Bundle\ClientBundle\Service\Mbhs($this);
    }

    /**
     * Gets the 'mbh.mongo' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Service\Mongo A MBH\Bundle\BaseBundle\Service\Mongo instance.
     */
    protected function getMbh_MongoService()
    {
        return $this->services['mbh.mongo'] = new \MBH\Bundle\BaseBundle\Service\Mongo($this);
    }

    /**
     * Gets the 'mbh.notifier' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Service\Messenger\Notifier A MBH\Bundle\BaseBundle\Service\Messenger\Notifier instance.
     */
    protected function getMbh_NotifierService()
    {
        $this->services['mbh.notifier'] = $instance = new \MBH\Bundle\BaseBundle\Service\Messenger\Notifier($this);

        $instance->attach($this->get('mbh.system.messenger'));
        $instance->attach($this->get('mbh.mailer'));

        return $instance;
    }

    /**
     * Gets the 'mbh.notifier.mailer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Service\Messenger\Notifier A MBH\Bundle\BaseBundle\Service\Messenger\Notifier instance.
     */
    protected function getMbh_Notifier_MailerService()
    {
        $this->services['mbh.notifier.mailer'] = $instance = new \MBH\Bundle\BaseBundle\Service\Messenger\Notifier($this);

        $instance->attach($this->get('mbh.mailer'));

        return $instance;
    }

    /**
     * Gets the 'mbh.online.logger' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMbh_Online_LoggerService()
    {
        $this->services['mbh.online.logger'] = $instance = new \Symfony\Bridge\Monolog\Logger('app');

        $instance->pushHandler($this->get('mbh.online.logger_handler'));

        return $instance;
    }

    /**
     * Gets the 'mbh.online.logger_handler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Monolog\Handler\StreamHandler A Monolog\Handler\StreamHandler instance.
     */
    protected function getMbh_Online_LoggerHandlerService()
    {
        return $this->services['mbh.online.logger_handler'] = new \Monolog\Handler\StreamHandler(($this->targetDirs[2].'/logs/dev.online.log'), 200);
    }

    /**
     * Gets the 'mbh.order_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Services\OrderManager A MBH\Bundle\PackageBundle\Services\OrderManager instance.
     */
    protected function getMbh_OrderManagerService()
    {
        return $this->services['mbh.order_manager'] = new \MBH\Bundle\PackageBundle\Services\OrderManager($this);
    }

    /**
     * Gets the 'mbh.package.document_factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\DocumentGenerator\ChainGeneratorFactory A MBH\Bundle\PackageBundle\DocumentGenerator\ChainGeneratorFactory instance.
     */
    protected function getMbh_Package_DocumentFactoryService()
    {
        $this->services['mbh.package.document_factory'] = $instance = new \MBH\Bundle\PackageBundle\DocumentGenerator\ChainGeneratorFactory();

        $instance->addFactory($this->get('mbh.package.document_tempalte_factory'));
        $instance->addFactory($this->get('mbh.package.document_xls_factory'));
        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'mbh.package.document_tempalte_factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\DocumentGenerator\Template\TemplateGeneratorFactory A MBH\Bundle\PackageBundle\DocumentGenerator\Template\TemplateGeneratorFactory instance.
     */
    protected function getMbh_Package_DocumentTempalteFactoryService()
    {
        $this->services['mbh.package.document_tempalte_factory'] = $instance = new \MBH\Bundle\PackageBundle\DocumentGenerator\Template\TemplateGeneratorFactory();

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'mbh.package.document_xls_factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\DocumentGenerator\Xls\XlsGeneratorFactory A MBH\Bundle\PackageBundle\DocumentGenerator\Xls\XlsGeneratorFactory instance.
     */
    protected function getMbh_Package_DocumentXlsFactoryService()
    {
        $this->services['mbh.package.document_xls_factory'] = $instance = new \MBH\Bundle\PackageBundle\DocumentGenerator\Xls\XlsGeneratorFactory();

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'mbh.package.form.type.address_object_decomposed' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Form\AddressObjectDecomposedType A MBH\Bundle\PackageBundle\Form\AddressObjectDecomposedType instance.
     */
    protected function getMbh_Package_Form_Type_AddressObjectDecomposedService()
    {
        return $this->services['mbh.package.form.type.address_object_decomposed'] = new \MBH\Bundle\PackageBundle\Form\AddressObjectDecomposedType();
    }

    /**
     * Gets the 'mbh.package.form.type.birthplace' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Form\BirthplaceType A MBH\Bundle\PackageBundle\Form\BirthplaceType instance.
     */
    protected function getMbh_Package_Form_Type_BirthplaceService()
    {
        return $this->services['mbh.package.form.type.birthplace'] = new \MBH\Bundle\PackageBundle\Form\BirthplaceType();
    }

    /**
     * Gets the 'mbh.package.form.type.document_relation' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Form\DocumentRelationType A MBH\Bundle\PackageBundle\Form\DocumentRelationType instance.
     */
    protected function getMbh_Package_Form_Type_DocumentRelationService()
    {
        $this->services['mbh.package.form.type.document_relation'] = $instance = new \MBH\Bundle\PackageBundle\Form\DocumentRelationType();

        $instance->setDictionaryProvider($this->get('mbh.vega.dictionary_provider'));
        $instance->setManagerRegistry($this->get('doctrine_mongodb'));

        return $instance;
    }

    /**
     * Gets the 'mbh.package.order.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\EventListener\OrderSubscriber A MBH\Bundle\PackageBundle\EventListener\OrderSubscriber instance.
     */
    protected function getMbh_Package_Order_SubscriberService()
    {
        return $this->services['mbh.package.order.subscriber'] = new \MBH\Bundle\PackageBundle\EventListener\OrderSubscriber($this);
    }

    /**
     * Gets the 'mbh.package.payer_repository' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Document\PayerRepository A MBH\Bundle\PackageBundle\Document\PayerRepository instance.
     */
    protected function getMbh_Package_PayerRepositoryService()
    {
        $this->services['mbh.package.payer_repository'] = $instance = new \MBH\Bundle\PackageBundle\Document\PayerRepository();

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'mbh.package.permissions' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Services\Permissions A MBH\Bundle\PackageBundle\Services\Permissions instance.
     */
    protected function getMbh_Package_PermissionsService()
    {
        return $this->services['mbh.package.permissions'] = new \MBH\Bundle\PackageBundle\Services\Permissions($this);
    }

    /**
     * Gets the 'mbh.package.report.filling_report_generator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Component\FillingReportGenerator A MBH\Bundle\PackageBundle\Component\FillingReportGenerator instance.
     */
    protected function getMbh_Package_Report_FillingReportGeneratorService()
    {
        $this->services['mbh.package.report.filling_report_generator'] = $instance = new \MBH\Bundle\PackageBundle\Component\FillingReportGenerator();

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'mbh.package.search' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Services\Search\SearchFactory A MBH\Bundle\PackageBundle\Services\Search\SearchFactory instance.
     */
    protected function getMbh_Package_SearchService()
    {
        return $this->services['mbh.package.search'] = new \MBH\Bundle\PackageBundle\Services\Search\SearchFactory($this);
    }

    /**
     * Gets the 'mbh.package.search_multiple_dates' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Services\Search\SearchMultipleDates A MBH\Bundle\PackageBundle\Services\Search\SearchMultipleDates instance.
     */
    protected function getMbh_Package_SearchMultipleDatesService()
    {
        return $this->services['mbh.package.search_multiple_dates'] = new \MBH\Bundle\PackageBundle\Services\Search\SearchMultipleDates($this);
    }

    /**
     * Gets the 'mbh.package.search_simple' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Services\Search\Search A MBH\Bundle\PackageBundle\Services\Search\Search instance.
     */
    protected function getMbh_Package_SearchSimpleService()
    {
        return $this->services['mbh.package.search_simple'] = new \MBH\Bundle\PackageBundle\Services\Search\Search($this);
    }

    /**
     * Gets the 'mbh.package.search_with_tariffs' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Services\Search\SearchWithTariffs A MBH\Bundle\PackageBundle\Services\Search\SearchWithTariffs instance.
     */
    protected function getMbh_Package_SearchWithTariffsService()
    {
        return $this->services['mbh.package.search_with_tariffs'] = new \MBH\Bundle\PackageBundle\Services\Search\SearchWithTariffs();
    }

    /**
     * Gets the 'mbh.package.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\EventListener\PackageSubscriber A MBH\Bundle\PackageBundle\EventListener\PackageSubscriber instance.
     */
    protected function getMbh_Package_SubscriberService()
    {
        return $this->services['mbh.package.subscriber'] = new \MBH\Bundle\PackageBundle\EventListener\PackageSubscriber($this);
    }

    /**
     * Gets the 'mbh.package.subscriber.tourist' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\EventListener\TouristSubscriber A MBH\Bundle\PackageBundle\EventListener\TouristSubscriber instance.
     */
    protected function getMbh_Package_Subscriber_TouristService()
    {
        $this->services['mbh.package.subscriber.tourist'] = $instance = new \MBH\Bundle\PackageBundle\EventListener\TouristSubscriber();

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'mbh.package.unwelcome_repository' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Document\UnwelcomeRepository A MBH\Bundle\PackageBundle\Document\UnwelcomeRepository instance.
     */
    protected function getMbh_Package_UnwelcomeRepositoryService()
    {
        return $this->services['mbh.package.unwelcome_repository'] = new \MBH\Bundle\PackageBundle\Document\UnwelcomeRepository($this->get('mbh.mbhs'));
    }

    /**
     * Gets the 'mbh.package.validator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Validator\Constraints\PackageValidator A MBH\Bundle\PackageBundle\Validator\Constraints\PackageValidator instance.
     */
    protected function getMbh_Package_ValidatorService()
    {
        return $this->services['mbh.package.validator'] = new \MBH\Bundle\PackageBundle\Validator\Constraints\PackageValidator($this);
    }

    /**
     * Gets the 'mbh.pdf_generator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Service\PdfGenerator A MBH\Bundle\BaseBundle\Service\PdfGenerator instance.
     */
    protected function getMbh_PdfGeneratorService()
    {
        $this->services['mbh.pdf_generator'] = $instance = new \MBH\Bundle\BaseBundle\Service\PdfGenerator();

        $instance->setContainer($this);

        return $instance;
    }

    /**
     * Gets the 'mbh.price.cache' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PriceBundle\Services\PriceCache A MBH\Bundle\PriceBundle\Services\PriceCache instance.
     */
    protected function getMbh_Price_CacheService()
    {
        return $this->services['mbh.price.cache'] = new \MBH\Bundle\PriceBundle\Services\PriceCache($this);
    }

    /**
     * Gets the 'mbh.restriction' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PriceBundle\Services\Restriction A MBH\Bundle\PriceBundle\Services\Restriction instance.
     */
    protected function getMbh_RestrictionService()
    {
        return $this->services['mbh.restriction'] = new \MBH\Bundle\PriceBundle\Services\Restriction($this);
    }

    /**
     * Gets the 'mbh.room.cache' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PriceBundle\Services\RoomCache A MBH\Bundle\PriceBundle\Services\RoomCache instance.
     */
    protected function getMbh_Room_CacheService()
    {
        return $this->services['mbh.room.cache'] = new \MBH\Bundle\PriceBundle\Services\RoomCache($this);
    }

    /**
     * Gets the 'mbh.room.cache.graph.generator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PriceBundle\Services\RoomCacheGraphGenerator A MBH\Bundle\PriceBundle\Services\RoomCacheGraphGenerator instance.
     */
    protected function getMbh_Room_Cache_Graph_GeneratorService()
    {
        return $this->services['mbh.room.cache.graph.generator'] = new \MBH\Bundle\PriceBundle\Services\RoomCacheGraphGenerator($this->get('mbh.helper'), $this->get('doctrine_mongodb'));
    }

    /**
     * Gets the 'mbh.room_cache.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PriceBundle\EventListener\RoomCacheSubscriber A MBH\Bundle\PriceBundle\EventListener\RoomCacheSubscriber instance.
     */
    protected function getMbh_RoomCache_SubscriberService()
    {
        return $this->services['mbh.room_cache.subscriber'] = new \MBH\Bundle\PriceBundle\EventListener\RoomCacheSubscriber($this);
    }

    /**
     * Gets the 'mbh.system.messenger' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Service\Messenger\Messenger A MBH\Bundle\BaseBundle\Service\Messenger\Messenger instance.
     */
    protected function getMbh_System_MessengerService()
    {
        return $this->services['mbh.system.messenger'] = new \MBH\Bundle\BaseBundle\Service\Messenger\Messenger($this);
    }

    /**
     * Gets the 'mbh.tariff.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PriceBundle\EventListener\TariffSubscriber A MBH\Bundle\PriceBundle\EventListener\TariffSubscriber instance.
     */
    protected function getMbh_Tariff_SubscriberService()
    {
        return $this->services['mbh.tariff.subscriber'] = new \MBH\Bundle\PriceBundle\EventListener\TariffSubscriber($this);
    }

    /**
     * Gets the 'mbh.testaurant.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\RestaurantBundle\EventListener\RestaurantSubscriber A MBH\Bundle\RestaurantBundle\EventListener\RestaurantSubscriber instance.
     */
    protected function getMbh_Testaurant_SubscriberService()
    {
        return $this->services['mbh.testaurant.subscriber'] = new \MBH\Bundle\RestaurantBundle\EventListener\RestaurantSubscriber($this);
    }

    /**
     * Gets the 'mbh.tourists.messenger' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\PackageBundle\Services\TouristsMessenger A MBH\Bundle\PackageBundle\Services\TouristsMessenger instance.
     */
    protected function getMbh_Tourists_MessengerService()
    {
        return $this->services['mbh.tourists.messenger'] = new \MBH\Bundle\PackageBundle\Services\TouristsMessenger($this);
    }

    /**
     * Gets the 'mbh.twig.extension' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Twig\Extension A MBH\Bundle\BaseBundle\Twig\Extension instance.
     */
    protected function getMbh_Twig_ExtensionService()
    {
        return $this->services['mbh.twig.extension'] = new \MBH\Bundle\BaseBundle\Twig\Extension($this);
    }

    /**
     * Gets the 'mbh.twig.hotel_selector_extension' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Twig\HotelSelectorExtension A MBH\Bundle\BaseBundle\Twig\HotelSelectorExtension instance.
     */
    protected function getMbh_Twig_HotelSelectorExtensionService()
    {
        return $this->services['mbh.twig.hotel_selector_extension'] = new \MBH\Bundle\BaseBundle\Twig\HotelSelectorExtension($this);
    }

    /**
     * Gets the 'mbh.user.group.type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\UserBundle\Form\GroupType A MBH\Bundle\UserBundle\Form\GroupType instance.
     */
    protected function getMbh_User_Group_TypeService()
    {
        return $this->services['mbh.user.group.type'] = new \MBH\Bundle\UserBundle\Form\GroupType(array('ROLE_SUPER_ADMIN' => array(0 => 'ROLE_ADMIN'), 'ROLE_ADMIN' => array(0 => 'ROLE_HOTEL', 1 => 'ROLE_GROUP', 2 => 'ROLE_CITY', 3 => 'ROLE_LOGS', 4 => 'ROLE_CASH', 5 => 'ROLE_CLIENT_CONFIG', 6 => 'ROLE_DOCUMENT_TEMPLATES', 7 => 'ROLE_HOUSING', 8 => 'ROLE_ROOM', 9 => 'ROLE_ROOM_TYPE', 10 => 'ROLE_TASK_MANAGER', 11 => 'ROLE_MANAGER', 12 => 'ROLE_OVERVIEW', 13 => 'ROLE_PRICE_CACHE', 14 => 'ROLE_RESTRICTION', 15 => 'ROLE_ROOM_CACHE', 16 => 'ROLE_SERVICE', 17 => 'ROLE_SERVICE_CATEGORY', 18 => 'ROLE_TARIFF', 19 => 'ROLE_USER', 20 => 'ROLE_CHANNEL_MANAGER', 21 => 'ROLE_ONLINE_FORM', 22 => 'ROLE_POLLS', 23 => 'ROLE_REPORTS', 24 => 'ROLE_PACKAGE', 25 => 'ROLE_SOURCE', 26 => 'ROLE_PROMOTION', 27 => 'ROLE_ROOM_TYPE_CATEGORY', 28 => 'ROLE_WORK_SHIFT', 29 => 'ROLE_WAREHOUSE', 30 => 'ROLE_RESTAURANT'), 'ROLE_LOGS' => array(0 => 'ROLE_BASE_USER'), 'ROLE_USER' => array(0 => 'ROLE_USER_VIEW', 1 => 'ROLE_USER_NEW', 2 => 'ROLE_USER_EDIT', 3 => 'ROLE_USER_DELETE', 4 => 'ROLE_USER_PROFILE'), 'ROLE_GROUP' => array(0 => 'ROLE_GROUP_VIEW', 1 => 'ROLE_GROUP_NEW', 2 => 'ROLE_GROUP_EDIT', 3 => 'ROLE_GROUP_DELETE'), 'ROLE_HOTEL' => array(0 => 'ROLE_HOTEL_VIEW', 1 => 'ROLE_HOTEL_NEW', 2 => 'ROLE_HOTEL_EDIT', 3 => 'ROLE_HOTEL_DELETE'), 'ROLE_CITY' => array(0 => 'ROLE_CITY_VIEW'), 'ROLE_CASH' => array(0 => 'ROLE_CASH_VIEW', 1 => 'ROLE_CASH_NEW', 2 => 'ROLE_CASH_EDIT', 3 => 'ROLE_CASH_DELETE', 4 => 'ROLE_CASH_CONFIRM', 5 => 'ROLE_CASH_NUMBER'), 'ROLE_CLIENT_CONFIG' => array(0 => 'ROLE_CLIENT_CONFIG_VIEW', 1 => 'ROLE_CLIENT_CONFIG_EDIT'), 'ROLE_DOCUMENT_TEMPLATES' => array(0 => 'ROLE_DOCUMENT_TEMPLATES_VIEW', 1 => 'ROLE_DOCUMENT_TEMPLATES_NEW', 2 => 'ROLE_DOCUMENT_TEMPLATES_EDIT', 3 => 'ROLE_DOCUMENT_TEMPLATES_DELETE'), 'ROLE_ROOM_FUND' => array(0 => 'ROLE_ROOM_TYPE', 1 => 'ROLE_ROOM'), 'ROLE_HOUSING' => array(0 => 'ROLE_HOUSING_VIEW', 1 => 'ROLE_HOUSING_NEW', 2 => 'ROLE_HOUSING_EDIT', 3 => 'ROLE_HOUSING_DELETE'), 'ROLE_ROOM_TYPE' => array(0 => 'ROLE_ROOM_TYPE_VIEW', 1 => 'ROLE_ROOM_TYPE_NEW', 2 => 'ROLE_ROOM_TYPE_EDIT', 3 => 'ROLE_ROOM_TYPE_DELETE'), 'ROLE_ROOM' => array(0 => 'ROLE_ROOM_VIEW', 1 => 'ROLE_ROOM_NEW', 2 => 'ROLE_ROOM_EDIT', 3 => 'ROLE_ROOM_DELETE', 4 => 'ROLE_ROOM_STATUS_EDIT'), 'ROLE_CHANNEL_MANAGER' => array(0 => 'ROLE_BOOKING', 1 => 'ROLE_VASHOTEL', 2 => 'ROLE_OSTROVOK', 3 => 'ROLE_OKTOGO', 4 => 'ROLE_HOTELINN', 5 => 'ROLE_MYALLOCATOR'), 'ROLE_ONLINE_FORM' => array(0 => 'ROLE_ONLINE_FORM_VIEW', 1 => 'ROLE_ONLINE_FORM_NEW', 2 => 'ROLE_ONLINE_FORM_EDIT', 3 => 'ROLE_ONLINE_FORM_DELETE'), 'ROLE_POLLS' => array(0 => 'ROLE_BASE_USER'), 'ROLE_PACKAGE' => array(0 => 'ROLE_SEARCH', 1 => 'ROLE_PACKAGE_VIEW', 2 => 'ROLE_PACKAGE_NEW', 3 => 'ROLE_ORDER_EDIT', 4 => 'ROLE_PACKAGE_EDIT', 5 => 'ROLE_ORDER_PAYER', 6 => 'ROLE_PACKAGE_GUESTS', 7 => 'ROLE_PACKAGE_SERVICES', 8 => 'ROLE_PACKAGE_ACCOMMODATION', 9 => 'ROLE_ORDER_DOCUMENTS', 10 => 'ROLE_DOCUMENTS_GENERATOR', 11 => 'ROLE_ORDER_CASH_DOCUMENTS', 12 => 'ROLE_PACKAGE_DELETE', 13 => 'ROLE_PACKAGE_VIEW_ALL', 14 => 'ROLE_PACKAGE_EDIT_ALL', 15 => 'ROLE_PACKAGE_DELETE_ALL', 16 => 'ROLE_PACKAGE_DOCS', 17 => 'ROLE_ORDER_AUTO_CONFIRMATION', 18 => 'ROLE_INDIVIDUAL_PROMOTION_ADD', 19 => 'ROLE_PROMOTION_ADD', 20 => 'ROLE_DISCOUNT_ADD', 21 => 'ROLE_PACKAGE_PRICE_EDIT', 22 => 'ROLE_FORCE_BOOKING'), 'ROLE_PACKAGE_VIEW_ALL' => array(0 => 'ROLE_PACKAGE_VIEW'), 'ROLE_PACKAGE_DELETE_ALL' => array(0 => 'ROLE_PACKAGE_DELETE'), 'ROLE_REPORTS' => array(0 => 'ROLE_ANALYTICS', 1 => 'ROLE_TOURIST_REPORT', 2 => 'ROLE_PORTER_REPORT', 3 => 'ROLE_ACCOMMODATION_REPORT', 4 => 'ROLE_SERVICES_REPORT', 5 => 'ROLE_ORGANIZATION', 6 => 'ROLE_MANAGERS_REPORT', 7 => 'ROLE_POLLS_REPORT', 8 => 'ROLE_ROOMS_REPORT'), 'ROLE_TOURIST_REPORT' => array(0 => 'ROLE_TOURIST'), 'ROLE_TOURIST' => array(0 => 'ROLE_TOURIST_VIEW', 1 => 'ROLE_TOURIST_NEW', 2 => 'ROLE_TOURIST_EDIT', 3 => 'ROLE_TOURIST_DELETE'), 'ROLE_ORGANIZATION' => array(0 => 'ROLE_ORGANIZATION_VIEW', 1 => 'ROLE_ORGANIZATION_NEW', 2 => 'ROLE_ORGANIZATION_EDIT', 3 => 'ROLE_ORGANIZATION_DELETE'), 'ROLE_SOURCE' => array(0 => 'ROLE_SOURCE_VIEW', 1 => 'ROLE_SOURCE_NEW', 2 => 'ROLE_SOURCE_EDIT', 3 => 'ROLE_SOURCE_DELETE'), 'ROLE_TASK_MANAGER' => array(0 => 'ROLE_TASK', 1 => 'ROLE_TASK_TYPE', 2 => 'ROLE_TASK_TYPE_CATEGORY'), 'ROLE_TASK' => array(0 => 'ROLE_TASK_VIEW', 1 => 'ROLE_TASK_OWN_VIEW', 2 => 'ROLE_TASK_NEW', 3 => 'ROLE_TASK_EDIT', 4 => 'ROLE_TASK_DELETE'), 'ROLE_TASK_TYPE' => array(0 => 'ROLE_TASK_TYPE_VIEW', 1 => 'ROLE_TASK_TYPE_NEW', 2 => 'ROLE_TASK_TYPE_EDIT', 3 => 'ROLE_TASK_TYPE_DELETE'), 'ROLE_TASK_TYPE_CATEGORY' => array(0 => 'ROLE_TASK_TYPE_CATEGORY_VIEW', 1 => 'ROLE_TASK_TYPE_CATEGORY_NEW', 2 => 'ROLE_TASK_TYPE_CATEGORY_EDIT', 3 => 'ROLE_TASK_TYPE_CATEGORY_DELETE'), 'ROLE_PRICE_CACHE' => array(0 => 'ROLE_PRICE_CACHE_VIEW', 1 => 'ROLE_PRICE_CACHE_EDIT', 2 => 'ROLE_OVERVIEW'), 'ROLE_RESTRICTION' => array(0 => 'ROLE_RESTRICTION_VIEW', 1 => 'ROLE_RESTRICTION_EDIT', 2 => 'ROLE_OVERVIEW'), 'ROLE_ROOM_CACHE' => array(0 => 'ROLE_ROOM_CACHE_VIEW', 1 => 'ROLE_ROOM_CACHE_EDIT', 2 => 'ROLE_OVERVIEW'), 'ROLE_SERVICE' => array(0 => 'ROLE_SERVICE_VIEW', 1 => 'ROLE_SERVICE_NEW', 2 => 'ROLE_SERVICE_EDIT', 3 => 'ROLE_SERVICE_DELETE'), 'ROLE_SERVICE_CATEGORY' => array(0 => 'ROLE_SERVICE_CATEGORY_NEW', 1 => 'ROLE_SERVICE_CATEGORY_EDIT', 2 => 'ROLE_SERVICE_CATEGORY_DELETE'), 'ROLE_TARIFF' => array(0 => 'ROLE_TARIFF_VIEW', 1 => 'ROLE_TARIFF_NEW', 2 => 'ROLE_TARIFF_EDIT', 3 => 'ROLE_TARIFF_DELETE'), 'ROLE_PROMOTION' => array(0 => 'ROLE_PROMOTION_VIEW', 1 => 'ROLE_PROMOTION_NEW', 2 => 'ROLE_PROMOTION_EDIT', 3 => 'ROLE_PROMOTION_DELETE'), 'ROLE_ROOM_TYPE_CATEGORY' => array(0 => 'ROLE_ROOM_TYPE_CATEGORY_VIEW', 1 => 'ROLE_ROOM_TYPE_CATEGORY_NEW', 2 => 'ROLE_ROOM_TYPE_CATEGORY_EDIT', 3 => 'ROLE_ROOM_TYPE_CATEGORY_DELETE'), 'ROLE_WORK_SHIFT' => array(0 => 'ROLE_WORK_SHIFT_VIEW', 1 => 'ROLE_WORK_SHIFT_CLOSE'), 'ROLE_STAFF' => array(0 => 'ROLE_TASK_OWN_VIEW'), 'ROLE_WAREHOUSE' => array(0 => 'ROLE_WAREHOUSE_CAT', 1 => 'ROLE_WAREHOUSE_ITEMS', 2 => 'ROLE_WAREHOUSE_RECORD', 3 => 'ROLE_WAREHOUSE_INVOICE'), 'ROLE_WAREHOUSE_CAT' => array(0 => 'ROLE_WAREHOUSE_CAT_VIEW', 1 => 'ROLE_WAREHOUSE_CAT_NEW', 2 => 'ROLE_WAREHOUSE_CAT_EDIT', 3 => 'ROLE_WAREHOUSE_CAT_DELETE'), 'ROLE_WAREHOUSE_ITEMS' => array(0 => 'ROLE_WAREHOUSE_ITEMS_VIEW', 1 => 'ROLE_WAREHOUSE_ITEMS_NEW', 2 => 'ROLE_WAREHOUSE_ITEMS_EDIT', 3 => 'ROLE_WAREHOUSE_ITEMS_DELETE'), 'ROLE_WAREHOUSE_RECORD' => array(0 => 'ROLE_WAREHOUSE_RECORD_VIEW', 1 => 'ROLE_WAREHOUSE_RECORD_NEW', 2 => 'ROLE_WAREHOUSE_RECORD_EDIT', 3 => 'ROLE_WAREHOUSE_RECORD_DELETE'), 'ROLE_WAREHOUSE_INVOICE' => array(0 => 'ROLE_WAREHOUSE_INVOICE_VIEW', 1 => 'ROLE_WAREHOUSE_INVOICE_NEW', 2 => 'ROLE_WAREHOUSE_INVOICE_EDIT', 3 => 'ROLE_WAREHOUSE_INVOICE_DELETE'), 'ROLE_RESTAURANT' => array(0 => 'ROLE_RESTAURANT_CATEGORY', 1 => 'ROLE_RESTAURANT_INGREDIENT', 2 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY', 3 => 'ROLE_RESTAURANT_DISHMENU_ITEM', 4 => 'ROLE_RESTAURANT_ORDER_MANAGER', 5 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED', 6 => 'ROLE_RESTAURANT_TABLE'), 'ROLE_RESTAURANT_CATEGORY' => array(0 => 'ROLE_RESTAURANT_CATEGORY_NEW', 1 => 'ROLE_RESTAURANT_CATEGORY_EDIT', 2 => 'ROLE_RESTAURANT_CATEGORY_DELETE'), 'ROLE_RESTAURANT_INGREDIENT' => array(0 => 'ROLE_RESTAURANT_INGREDIENT_VIEW', 1 => 'ROLE_RESTAURANT_INGREDIENT_NEW', 2 => 'ROLE_RESTAURANT_INGREDIENT_EDIT', 3 => 'ROLE_RESTAURANT_INGREDIENT_DELETE'), 'ROLE_RESTAURANT_DISHMENU_CATEGORY' => array(0 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_NEW', 1 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_EDIT', 2 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_DELETE'), 'ROLE_RESTAURANT_DISHMENU_ITEM' => array(0 => 'ROLE_RESTAURANT_DISHMENU_ITEM_VIEW', 1 => 'ROLE_RESTAURANT_DISHMENU_ITEM_NEW', 2 => 'ROLE_RESTAURANT_DISHMENU_ITEM_EDIT', 3 => 'ROLE_RESTAURANT_DISHMENU_ITEM_DELETE'), 'ROLE_RESTAURANT_ORDER_MANAGER' => array(0 => 'ROLE_RESTAURANT_ORDER_MANAGER_VIEW', 1 => 'ROLE_RESTAURANT_ORDER_MANAGER_NEW', 2 => 'ROLE_RESTAURANT_ORDER_MANAGER_EDIT', 3 => 'ROLE_RESTAURANT_ORDER_MANAGER_DELETE', 4 => 'ROLE_RESTAURANT_ORDER_MANAGER_PAY'), 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED' => array(0 => 'ROLE_RESTAURANT_ORDER_MANAGER', 1 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_EDIT', 2 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_DELETE'), 'ROLE_RESTAURANT_TABLE' => array(0 => 'ROLE_RESTAURANT_TABLE_VIEW', 1 => 'ROLE_RESTAURANT_TABLE_NEW', 2 => 'ROLE_RESTAURANT_TABLE_EDIT', 3 => 'ROLE_RESTAURANT_TABLE_DELETE')));
    }

    /**
     * Gets the 'mbh.user.metadata_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\UserBundle\EventListener\ClassMetadataListener A MBH\Bundle\UserBundle\EventListener\ClassMetadataListener instance.
     */
    protected function getMbh_User_MetadataListenerService()
    {
        return $this->services['mbh.user.metadata_listener'] = new \MBH\Bundle\UserBundle\EventListener\ClassMetadataListener();
    }

    /**
     * Gets the 'mbh.user.roles.type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\UserBundle\Form\Type\RolesType A MBH\Bundle\UserBundle\Form\Type\RolesType instance.
     */
    protected function getMbh_User_Roles_TypeService()
    {
        return $this->services['mbh.user.roles.type'] = new \MBH\Bundle\UserBundle\Form\Type\RolesType(array('ROLE_SUPER_ADMIN' => array(0 => 'ROLE_ADMIN'), 'ROLE_ADMIN' => array(0 => 'ROLE_HOTEL', 1 => 'ROLE_GROUP', 2 => 'ROLE_CITY', 3 => 'ROLE_LOGS', 4 => 'ROLE_CASH', 5 => 'ROLE_CLIENT_CONFIG', 6 => 'ROLE_DOCUMENT_TEMPLATES', 7 => 'ROLE_HOUSING', 8 => 'ROLE_ROOM', 9 => 'ROLE_ROOM_TYPE', 10 => 'ROLE_TASK_MANAGER', 11 => 'ROLE_MANAGER', 12 => 'ROLE_OVERVIEW', 13 => 'ROLE_PRICE_CACHE', 14 => 'ROLE_RESTRICTION', 15 => 'ROLE_ROOM_CACHE', 16 => 'ROLE_SERVICE', 17 => 'ROLE_SERVICE_CATEGORY', 18 => 'ROLE_TARIFF', 19 => 'ROLE_USER', 20 => 'ROLE_CHANNEL_MANAGER', 21 => 'ROLE_ONLINE_FORM', 22 => 'ROLE_POLLS', 23 => 'ROLE_REPORTS', 24 => 'ROLE_PACKAGE', 25 => 'ROLE_SOURCE', 26 => 'ROLE_PROMOTION', 27 => 'ROLE_ROOM_TYPE_CATEGORY', 28 => 'ROLE_WORK_SHIFT', 29 => 'ROLE_WAREHOUSE', 30 => 'ROLE_RESTAURANT'), 'ROLE_LOGS' => array(0 => 'ROLE_BASE_USER'), 'ROLE_USER' => array(0 => 'ROLE_USER_VIEW', 1 => 'ROLE_USER_NEW', 2 => 'ROLE_USER_EDIT', 3 => 'ROLE_USER_DELETE', 4 => 'ROLE_USER_PROFILE'), 'ROLE_GROUP' => array(0 => 'ROLE_GROUP_VIEW', 1 => 'ROLE_GROUP_NEW', 2 => 'ROLE_GROUP_EDIT', 3 => 'ROLE_GROUP_DELETE'), 'ROLE_HOTEL' => array(0 => 'ROLE_HOTEL_VIEW', 1 => 'ROLE_HOTEL_NEW', 2 => 'ROLE_HOTEL_EDIT', 3 => 'ROLE_HOTEL_DELETE'), 'ROLE_CITY' => array(0 => 'ROLE_CITY_VIEW'), 'ROLE_CASH' => array(0 => 'ROLE_CASH_VIEW', 1 => 'ROLE_CASH_NEW', 2 => 'ROLE_CASH_EDIT', 3 => 'ROLE_CASH_DELETE', 4 => 'ROLE_CASH_CONFIRM', 5 => 'ROLE_CASH_NUMBER'), 'ROLE_CLIENT_CONFIG' => array(0 => 'ROLE_CLIENT_CONFIG_VIEW', 1 => 'ROLE_CLIENT_CONFIG_EDIT'), 'ROLE_DOCUMENT_TEMPLATES' => array(0 => 'ROLE_DOCUMENT_TEMPLATES_VIEW', 1 => 'ROLE_DOCUMENT_TEMPLATES_NEW', 2 => 'ROLE_DOCUMENT_TEMPLATES_EDIT', 3 => 'ROLE_DOCUMENT_TEMPLATES_DELETE'), 'ROLE_ROOM_FUND' => array(0 => 'ROLE_ROOM_TYPE', 1 => 'ROLE_ROOM'), 'ROLE_HOUSING' => array(0 => 'ROLE_HOUSING_VIEW', 1 => 'ROLE_HOUSING_NEW', 2 => 'ROLE_HOUSING_EDIT', 3 => 'ROLE_HOUSING_DELETE'), 'ROLE_ROOM_TYPE' => array(0 => 'ROLE_ROOM_TYPE_VIEW', 1 => 'ROLE_ROOM_TYPE_NEW', 2 => 'ROLE_ROOM_TYPE_EDIT', 3 => 'ROLE_ROOM_TYPE_DELETE'), 'ROLE_ROOM' => array(0 => 'ROLE_ROOM_VIEW', 1 => 'ROLE_ROOM_NEW', 2 => 'ROLE_ROOM_EDIT', 3 => 'ROLE_ROOM_DELETE', 4 => 'ROLE_ROOM_STATUS_EDIT'), 'ROLE_CHANNEL_MANAGER' => array(0 => 'ROLE_BOOKING', 1 => 'ROLE_VASHOTEL', 2 => 'ROLE_OSTROVOK', 3 => 'ROLE_OKTOGO', 4 => 'ROLE_HOTELINN', 5 => 'ROLE_MYALLOCATOR'), 'ROLE_ONLINE_FORM' => array(0 => 'ROLE_ONLINE_FORM_VIEW', 1 => 'ROLE_ONLINE_FORM_NEW', 2 => 'ROLE_ONLINE_FORM_EDIT', 3 => 'ROLE_ONLINE_FORM_DELETE'), 'ROLE_POLLS' => array(0 => 'ROLE_BASE_USER'), 'ROLE_PACKAGE' => array(0 => 'ROLE_SEARCH', 1 => 'ROLE_PACKAGE_VIEW', 2 => 'ROLE_PACKAGE_NEW', 3 => 'ROLE_ORDER_EDIT', 4 => 'ROLE_PACKAGE_EDIT', 5 => 'ROLE_ORDER_PAYER', 6 => 'ROLE_PACKAGE_GUESTS', 7 => 'ROLE_PACKAGE_SERVICES', 8 => 'ROLE_PACKAGE_ACCOMMODATION', 9 => 'ROLE_ORDER_DOCUMENTS', 10 => 'ROLE_DOCUMENTS_GENERATOR', 11 => 'ROLE_ORDER_CASH_DOCUMENTS', 12 => 'ROLE_PACKAGE_DELETE', 13 => 'ROLE_PACKAGE_VIEW_ALL', 14 => 'ROLE_PACKAGE_EDIT_ALL', 15 => 'ROLE_PACKAGE_DELETE_ALL', 16 => 'ROLE_PACKAGE_DOCS', 17 => 'ROLE_ORDER_AUTO_CONFIRMATION', 18 => 'ROLE_INDIVIDUAL_PROMOTION_ADD', 19 => 'ROLE_PROMOTION_ADD', 20 => 'ROLE_DISCOUNT_ADD', 21 => 'ROLE_PACKAGE_PRICE_EDIT', 22 => 'ROLE_FORCE_BOOKING'), 'ROLE_PACKAGE_VIEW_ALL' => array(0 => 'ROLE_PACKAGE_VIEW'), 'ROLE_PACKAGE_DELETE_ALL' => array(0 => 'ROLE_PACKAGE_DELETE'), 'ROLE_REPORTS' => array(0 => 'ROLE_ANALYTICS', 1 => 'ROLE_TOURIST_REPORT', 2 => 'ROLE_PORTER_REPORT', 3 => 'ROLE_ACCOMMODATION_REPORT', 4 => 'ROLE_SERVICES_REPORT', 5 => 'ROLE_ORGANIZATION', 6 => 'ROLE_MANAGERS_REPORT', 7 => 'ROLE_POLLS_REPORT', 8 => 'ROLE_ROOMS_REPORT'), 'ROLE_TOURIST_REPORT' => array(0 => 'ROLE_TOURIST'), 'ROLE_TOURIST' => array(0 => 'ROLE_TOURIST_VIEW', 1 => 'ROLE_TOURIST_NEW', 2 => 'ROLE_TOURIST_EDIT', 3 => 'ROLE_TOURIST_DELETE'), 'ROLE_ORGANIZATION' => array(0 => 'ROLE_ORGANIZATION_VIEW', 1 => 'ROLE_ORGANIZATION_NEW', 2 => 'ROLE_ORGANIZATION_EDIT', 3 => 'ROLE_ORGANIZATION_DELETE'), 'ROLE_SOURCE' => array(0 => 'ROLE_SOURCE_VIEW', 1 => 'ROLE_SOURCE_NEW', 2 => 'ROLE_SOURCE_EDIT', 3 => 'ROLE_SOURCE_DELETE'), 'ROLE_TASK_MANAGER' => array(0 => 'ROLE_TASK', 1 => 'ROLE_TASK_TYPE', 2 => 'ROLE_TASK_TYPE_CATEGORY'), 'ROLE_TASK' => array(0 => 'ROLE_TASK_VIEW', 1 => 'ROLE_TASK_OWN_VIEW', 2 => 'ROLE_TASK_NEW', 3 => 'ROLE_TASK_EDIT', 4 => 'ROLE_TASK_DELETE'), 'ROLE_TASK_TYPE' => array(0 => 'ROLE_TASK_TYPE_VIEW', 1 => 'ROLE_TASK_TYPE_NEW', 2 => 'ROLE_TASK_TYPE_EDIT', 3 => 'ROLE_TASK_TYPE_DELETE'), 'ROLE_TASK_TYPE_CATEGORY' => array(0 => 'ROLE_TASK_TYPE_CATEGORY_VIEW', 1 => 'ROLE_TASK_TYPE_CATEGORY_NEW', 2 => 'ROLE_TASK_TYPE_CATEGORY_EDIT', 3 => 'ROLE_TASK_TYPE_CATEGORY_DELETE'), 'ROLE_PRICE_CACHE' => array(0 => 'ROLE_PRICE_CACHE_VIEW', 1 => 'ROLE_PRICE_CACHE_EDIT', 2 => 'ROLE_OVERVIEW'), 'ROLE_RESTRICTION' => array(0 => 'ROLE_RESTRICTION_VIEW', 1 => 'ROLE_RESTRICTION_EDIT', 2 => 'ROLE_OVERVIEW'), 'ROLE_ROOM_CACHE' => array(0 => 'ROLE_ROOM_CACHE_VIEW', 1 => 'ROLE_ROOM_CACHE_EDIT', 2 => 'ROLE_OVERVIEW'), 'ROLE_SERVICE' => array(0 => 'ROLE_SERVICE_VIEW', 1 => 'ROLE_SERVICE_NEW', 2 => 'ROLE_SERVICE_EDIT', 3 => 'ROLE_SERVICE_DELETE'), 'ROLE_SERVICE_CATEGORY' => array(0 => 'ROLE_SERVICE_CATEGORY_NEW', 1 => 'ROLE_SERVICE_CATEGORY_EDIT', 2 => 'ROLE_SERVICE_CATEGORY_DELETE'), 'ROLE_TARIFF' => array(0 => 'ROLE_TARIFF_VIEW', 1 => 'ROLE_TARIFF_NEW', 2 => 'ROLE_TARIFF_EDIT', 3 => 'ROLE_TARIFF_DELETE'), 'ROLE_PROMOTION' => array(0 => 'ROLE_PROMOTION_VIEW', 1 => 'ROLE_PROMOTION_NEW', 2 => 'ROLE_PROMOTION_EDIT', 3 => 'ROLE_PROMOTION_DELETE'), 'ROLE_ROOM_TYPE_CATEGORY' => array(0 => 'ROLE_ROOM_TYPE_CATEGORY_VIEW', 1 => 'ROLE_ROOM_TYPE_CATEGORY_NEW', 2 => 'ROLE_ROOM_TYPE_CATEGORY_EDIT', 3 => 'ROLE_ROOM_TYPE_CATEGORY_DELETE'), 'ROLE_WORK_SHIFT' => array(0 => 'ROLE_WORK_SHIFT_VIEW', 1 => 'ROLE_WORK_SHIFT_CLOSE'), 'ROLE_STAFF' => array(0 => 'ROLE_TASK_OWN_VIEW'), 'ROLE_WAREHOUSE' => array(0 => 'ROLE_WAREHOUSE_CAT', 1 => 'ROLE_WAREHOUSE_ITEMS', 2 => 'ROLE_WAREHOUSE_RECORD', 3 => 'ROLE_WAREHOUSE_INVOICE'), 'ROLE_WAREHOUSE_CAT' => array(0 => 'ROLE_WAREHOUSE_CAT_VIEW', 1 => 'ROLE_WAREHOUSE_CAT_NEW', 2 => 'ROLE_WAREHOUSE_CAT_EDIT', 3 => 'ROLE_WAREHOUSE_CAT_DELETE'), 'ROLE_WAREHOUSE_ITEMS' => array(0 => 'ROLE_WAREHOUSE_ITEMS_VIEW', 1 => 'ROLE_WAREHOUSE_ITEMS_NEW', 2 => 'ROLE_WAREHOUSE_ITEMS_EDIT', 3 => 'ROLE_WAREHOUSE_ITEMS_DELETE'), 'ROLE_WAREHOUSE_RECORD' => array(0 => 'ROLE_WAREHOUSE_RECORD_VIEW', 1 => 'ROLE_WAREHOUSE_RECORD_NEW', 2 => 'ROLE_WAREHOUSE_RECORD_EDIT', 3 => 'ROLE_WAREHOUSE_RECORD_DELETE'), 'ROLE_WAREHOUSE_INVOICE' => array(0 => 'ROLE_WAREHOUSE_INVOICE_VIEW', 1 => 'ROLE_WAREHOUSE_INVOICE_NEW', 2 => 'ROLE_WAREHOUSE_INVOICE_EDIT', 3 => 'ROLE_WAREHOUSE_INVOICE_DELETE'), 'ROLE_RESTAURANT' => array(0 => 'ROLE_RESTAURANT_CATEGORY', 1 => 'ROLE_RESTAURANT_INGREDIENT', 2 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY', 3 => 'ROLE_RESTAURANT_DISHMENU_ITEM', 4 => 'ROLE_RESTAURANT_ORDER_MANAGER', 5 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED', 6 => 'ROLE_RESTAURANT_TABLE'), 'ROLE_RESTAURANT_CATEGORY' => array(0 => 'ROLE_RESTAURANT_CATEGORY_NEW', 1 => 'ROLE_RESTAURANT_CATEGORY_EDIT', 2 => 'ROLE_RESTAURANT_CATEGORY_DELETE'), 'ROLE_RESTAURANT_INGREDIENT' => array(0 => 'ROLE_RESTAURANT_INGREDIENT_VIEW', 1 => 'ROLE_RESTAURANT_INGREDIENT_NEW', 2 => 'ROLE_RESTAURANT_INGREDIENT_EDIT', 3 => 'ROLE_RESTAURANT_INGREDIENT_DELETE'), 'ROLE_RESTAURANT_DISHMENU_CATEGORY' => array(0 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_NEW', 1 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_EDIT', 2 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_DELETE'), 'ROLE_RESTAURANT_DISHMENU_ITEM' => array(0 => 'ROLE_RESTAURANT_DISHMENU_ITEM_VIEW', 1 => 'ROLE_RESTAURANT_DISHMENU_ITEM_NEW', 2 => 'ROLE_RESTAURANT_DISHMENU_ITEM_EDIT', 3 => 'ROLE_RESTAURANT_DISHMENU_ITEM_DELETE'), 'ROLE_RESTAURANT_ORDER_MANAGER' => array(0 => 'ROLE_RESTAURANT_ORDER_MANAGER_VIEW', 1 => 'ROLE_RESTAURANT_ORDER_MANAGER_NEW', 2 => 'ROLE_RESTAURANT_ORDER_MANAGER_EDIT', 3 => 'ROLE_RESTAURANT_ORDER_MANAGER_DELETE', 4 => 'ROLE_RESTAURANT_ORDER_MANAGER_PAY'), 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED' => array(0 => 'ROLE_RESTAURANT_ORDER_MANAGER', 1 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_EDIT', 2 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_DELETE'), 'ROLE_RESTAURANT_TABLE' => array(0 => 'ROLE_RESTAURANT_TABLE_VIEW', 1 => 'ROLE_RESTAURANT_TABLE_NEW', 2 => 'ROLE_RESTAURANT_TABLE_EDIT', 3 => 'ROLE_RESTAURANT_TABLE_DELETE')));
    }

    /**
     * Gets the 'mbh.user.validator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\UserBundle\Validator\Constraints\UserValidator A MBH\Bundle\UserBundle\Validator\Constraints\UserValidator instance.
     */
    protected function getMbh_User_ValidatorService()
    {
        return $this->services['mbh.user.validator'] = new \MBH\Bundle\UserBundle\Validator\Constraints\UserValidator($this);
    }

    /**
     * Gets the 'mbh.user.work_shift_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\UserBundle\EventListener\WorkShiftListener A MBH\Bundle\UserBundle\EventListener\WorkShiftListener instance.
     */
    protected function getMbh_User_WorkShiftListenerService()
    {
        return $this->services['mbh.user.work_shift_listener'] = new \MBH\Bundle\UserBundle\EventListener\WorkShiftListener($this->get('security.token_storage'), $this->get('session'), $this->get('router'), $this->get('mbh.user.work_shift_repository'), $this->get('translator.default'));
    }

    /**
     * Gets the 'mbh.user.work_shift_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\UserBundle\Service\WorkShiftManager A MBH\Bundle\UserBundle\Service\WorkShiftManager instance.
     */
    protected function getMbh_User_WorkShiftManagerService()
    {
        return $this->services['mbh.user.work_shift_manager'] = new \MBH\Bundle\UserBundle\Service\WorkShiftManager($this->get('doctrine_mongodb.odm.default_document_manager'), $this->get('mbh.hotel.selector'));
    }

    /**
     * Gets the 'mbh.user.work_shift_repository' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\ORM\DocumentRepository A Doctrine\ORM\DocumentRepository instance.
     */
    protected function getMbh_User_WorkShiftRepositoryService()
    {
        return $this->services['mbh.user.work_shift_repository'] = $this->get('doctrine_mongodb.odm.default_document_manager')->getRepository('MBH\\Bundle\\UserBundle\\Document\\WorkShift');
    }

    /**
     * Gets the 'mbh.validator.range' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\BaseBundle\Validator\Constraints\RangeValidator A MBH\Bundle\BaseBundle\Validator\Constraints\RangeValidator instance.
     */
    protected function getMbh_Validator_RangeService()
    {
        return $this->services['mbh.validator.range'] = new \MBH\Bundle\BaseBundle\Validator\Constraints\RangeValidator();
    }

    /**
     * Gets the 'mbh.vega.dictionary_provider' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\VegaBundle\Service\DictionaryProvider A MBH\Bundle\VegaBundle\Service\DictionaryProvider instance.
     */
    protected function getMbh_Vega_DictionaryProviderService()
    {
        return $this->services['mbh.vega.dictionary_provider'] = new \MBH\Bundle\VegaBundle\Service\DictionaryProvider();
    }

    /**
     * Gets the 'mbh.vega.vega_export' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\VegaBundle\Service\VegaExport A MBH\Bundle\VegaBundle\Service\VegaExport instance.
     */
    protected function getMbh_Vega_VegaExportService()
    {
        return $this->services['mbh.vega.vega_export'] = new \MBH\Bundle\VegaBundle\Service\VegaExport($this);
    }

    /**
     * Gets the 'mbh.warehouse.subscriber' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\WarehouseBundle\EventListener\WarehouseSubscriber A MBH\Bundle\WarehouseBundle\EventListener\WarehouseSubscriber instance.
     */
    protected function getMbh_Warehouse_SubscriberService()
    {
        return $this->services['mbh.warehouse.subscriber'] = new \MBH\Bundle\WarehouseBundle\EventListener\WarehouseSubscriber($this);
    }

    /**
     * Gets the 'mbh__restaurant.form.dish_menu_ingredient_embedded_type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\RestaurantBundle\Form\DishMenuIngredientEmbeddedType A MBH\Bundle\RestaurantBundle\Form\DishMenuIngredientEmbeddedType instance.
     */
    protected function getMbhRestaurant_Form_DishMenuIngredientEmbeddedTypeService()
    {
        return $this->services['mbh__restaurant.form.dish_menu_ingredient_embedded_type'] = new \MBH\Bundle\RestaurantBundle\Form\DishMenuIngredientEmbeddedType($this);
    }

    /**
     * Gets the 'mbh__restaurant.form_dish_order.dish_order_item_emmbedded_type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\RestaurantBundle\Form\DishOrder\DishOrderItemEmmbeddedType A MBH\Bundle\RestaurantBundle\Form\DishOrder\DishOrderItemEmmbeddedType instance.
     */
    protected function getMbhRestaurant_FormDishOrder_DishOrderItemEmmbeddedTypeService()
    {
        return $this->services['mbh__restaurant.form_dish_order.dish_order_item_emmbedded_type'] = new \MBH\Bundle\RestaurantBundle\Form\DishOrder\DishOrderItemEmmbeddedType($this->get('request_stack'), $this->get('mbh.hotel.selector'), $this->get('mbh.helper'));
    }

    /**
     * Gets the 'mbh__restaurant.form_dish_order.dish_order_item_type' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\RestaurantBundle\Form\DishOrder\DishOrderItemType A MBH\Bundle\RestaurantBundle\Form\DishOrder\DishOrderItemType instance.
     */
    protected function getMbhRestaurant_FormDishOrder_DishOrderItemTypeService()
    {
        return $this->services['mbh__restaurant.form_dish_order.dish_order_item_type'] = new \MBH\Bundle\RestaurantBundle\Form\DishOrder\DishOrderItemType($this);
    }

    /**
     * Gets the 'memcache.data_collector' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Lsw\MemcacheBundle\DataCollector\MemcacheDataCollector A Lsw\MemcacheBundle\DataCollector\MemcacheDataCollector instance.
     */
    protected function getMemcache_DataCollectorService()
    {
        $this->services['memcache.data_collector'] = $instance = new \Lsw\MemcacheBundle\DataCollector\MemcacheDataCollector();

        $instance->addClient('default', array('allow_failover' => 'true', 'max_failover_attempts' => '20', 'default_port' => '11211', 'chunk_size' => '32768', 'protocol' => '\'ascii\'', 'hash_strategy' => '\'consistent\'', 'hash_function' => '\'crc32\'', 'redundancy' => 'true', 'session_redundancy' => '2', 'compress_threshold' => '20000', 'lock_timeout' => '15'), $this->get('memcache.default'));

        return $instance;
    }

    /**
     * Gets the 'memcache.default' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Lsw\MemcacheBundle\Cache\AntiDogPileMemcache A Lsw\MemcacheBundle\Cache\AntiDogPileMemcache instance.
     */
    protected function getMemcache_DefaultService()
    {
        $this->services['memcache.default'] = $instance = new \Lsw\MemcacheBundle\Cache\AntiDogPileMemcache(true, array('allow_failover' => true, 'max_failover_attempts' => 20, 'default_port' => 11211, 'chunk_size' => 32768, 'protocol' => 'ascii', 'hash_strategy' => 'consistent', 'hash_function' => 'crc32', 'redundancy' => true, 'session_redundancy' => 2, 'compress_threshold' => 20000, 'lock_timeout' => 15));

        $instance->addServer('localhost', 11211, 0, true, 1, 1, 15);

        return $instance;
    }

    /**
     * Gets the 'memcache.session_handler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Lsw\MemcacheBundle\Session\Storage\LockingSessionHandler A Lsw\MemcacheBundle\Session\Storage\LockingSessionHandler instance.
     */
    protected function getMemcache_SessionHandlerService()
    {
        return $this->services['memcache.session_handler'] = new \Lsw\MemcacheBundle\Session\Storage\LockingSessionHandler($this->get('memcache.default'), array('prefix' => 'lmbs', 'locking' => true, 'spin_lock_wait' => 150000, 'lock_max_wait' => NULL));
    }

    /**
     * Gets the 'misd_guzzle.cache.doctrine.filesystem' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Guzzle\Cache\DoctrineCacheAdapter A Guzzle\Cache\DoctrineCacheAdapter instance.
     */
    protected function getMisdGuzzle_Cache_Doctrine_FilesystemService()
    {
        return $this->services['misd_guzzle.cache.doctrine.filesystem'] = new \Guzzle\Cache\DoctrineCacheAdapter($this->get('misd_guzzle.cache.doctrine.filesystem.adapter'));
    }

    /**
     * Gets the 'misd_guzzle.cache.doctrine.filesystem.adapter' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Doctrine\Common\Cache\FilesystemCache A Doctrine\Common\Cache\FilesystemCache instance.
     */
    protected function getMisdGuzzle_Cache_Doctrine_Filesystem_AdapterService()
    {
        return $this->services['misd_guzzle.cache.doctrine.filesystem.adapter'] = new \Doctrine\Common\Cache\FilesystemCache((__DIR__.'/guzzle/'));
    }

    /**
     * Gets the 'misd_guzzle.cache.filesystem' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Guzzle\Plugin\Cache\CachePlugin A Guzzle\Plugin\Cache\CachePlugin instance.
     */
    protected function getMisdGuzzle_Cache_FilesystemService()
    {
        return $this->services['misd_guzzle.cache.filesystem'] = new \Guzzle\Plugin\Cache\CachePlugin($this->get('misd_guzzle.cache.doctrine.filesystem'));
    }

    /**
     * Gets the 'misd_guzzle.listener.command_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Misd\GuzzleBundle\EventListener\CommandListener A Misd\GuzzleBundle\EventListener\CommandListener instance.
     */
    protected function getMisdGuzzle_Listener_CommandListenerService()
    {
        return $this->services['misd_guzzle.listener.command_listener'] = new \Misd\GuzzleBundle\EventListener\CommandListener(NULL, $this->get('misd_guzzle.request.visitor.body'), $this->get('misd_guzzle.response.parser'));
    }

    /**
     * Gets the 'misd_guzzle.param_converter' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Misd\GuzzleBundle\Request\ParamConverter\GuzzleParamConverter3x A Misd\GuzzleBundle\Request\ParamConverter\GuzzleParamConverter3x instance.
     */
    protected function getMisdGuzzle_ParamConverterService()
    {
        return $this->services['misd_guzzle.param_converter'] = new \Misd\GuzzleBundle\Request\ParamConverter\GuzzleParamConverter3x();
    }

    /**
     * Gets the 'misd_guzzle.request.visitor.body' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Misd\GuzzleBundle\Service\Command\LocationVisitor\Request\JMSSerializerBodyVisitor A Misd\GuzzleBundle\Service\Command\LocationVisitor\Request\JMSSerializerBodyVisitor instance.
     */
    protected function getMisdGuzzle_Request_Visitor_BodyService()
    {
        return $this->services['misd_guzzle.request.visitor.body'] = new \Misd\GuzzleBundle\Service\Command\LocationVisitor\Request\JMSSerializerBodyVisitor(NULL);
    }

    /**
     * Gets the 'misd_guzzle.response.parser' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Misd\GuzzleBundle\Service\Command\JMSSerializerResponseParser A Misd\GuzzleBundle\Service\Command\JMSSerializerResponseParser instance.
     */
    protected function getMisdGuzzle_Response_ParserService()
    {
        return $this->services['misd_guzzle.response.parser'] = new \Misd\GuzzleBundle\Service\Command\JMSSerializerResponseParser(NULL, $this->get('misd_guzzle.response.parser.fallback'));
    }

    /**
     * Gets the 'misd_guzzle.response.parser.fallback' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Guzzle\Service\Command\OperationResponseParser A Guzzle\Service\Command\OperationResponseParser instance.
     */
    protected function getMisdGuzzle_Response_Parser_FallbackService()
    {
        return $this->services['misd_guzzle.response.parser.fallback'] = \Guzzle\Service\Command\OperationResponseParser::getInstance();
    }

    /**
     * Gets the 'monolog.handler.console' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Handler\ConsoleHandler A Symfony\Bridge\Monolog\Handler\ConsoleHandler instance.
     */
    protected function getMonolog_Handler_ConsoleService()
    {
        return $this->services['monolog.handler.console'] = new \Symfony\Bridge\Monolog\Handler\ConsoleHandler(NULL, false, array());
    }

    /**
     * Gets the 'monolog.handler.debug' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Handler\DebugHandler A Symfony\Bridge\Monolog\Handler\DebugHandler instance.
     */
    protected function getMonolog_Handler_DebugService()
    {
        return $this->services['monolog.handler.debug'] = new \Symfony\Bridge\Monolog\Handler\DebugHandler(100, true);
    }

    /**
     * Gets the 'monolog.handler.main' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Monolog\Handler\StreamHandler A Monolog\Handler\StreamHandler instance.
     */
    protected function getMonolog_Handler_MainService()
    {
        return $this->services['monolog.handler.main'] = new \Monolog\Handler\StreamHandler(($this->targetDirs[2].'/logs/dev.log'), 100, true, NULL);
    }

    /**
     * Gets the 'monolog.logger.assetic' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_AsseticService()
    {
        $this->services['monolog.logger.assetic'] = $instance = new \Symfony\Bridge\Monolog\Logger('assetic');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'monolog.logger.doctrine' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_DoctrineService()
    {
        $this->services['monolog.logger.doctrine'] = $instance = new \Symfony\Bridge\Monolog\Logger('doctrine');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'monolog.logger.event' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_EventService()
    {
        $this->services['monolog.logger.event'] = $instance = new \Symfony\Bridge\Monolog\Logger('event');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'monolog.logger.php' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_PhpService()
    {
        $this->services['monolog.logger.php'] = $instance = new \Symfony\Bridge\Monolog\Logger('php');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'monolog.logger.profiler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_ProfilerService()
    {
        $this->services['monolog.logger.profiler'] = $instance = new \Symfony\Bridge\Monolog\Logger('profiler');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'monolog.logger.request' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_RequestService()
    {
        $this->services['monolog.logger.request'] = $instance = new \Symfony\Bridge\Monolog\Logger('request');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'monolog.logger.router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_RouterService()
    {
        $this->services['monolog.logger.router'] = $instance = new \Symfony\Bridge\Monolog\Logger('router');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'monolog.logger.security' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_SecurityService()
    {
        $this->services['monolog.logger.security'] = $instance = new \Symfony\Bridge\Monolog\Logger('security');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'monolog.logger.snappy' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_SnappyService()
    {
        $this->services['monolog.logger.snappy'] = $instance = new \Symfony\Bridge\Monolog\Logger('snappy');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'monolog.logger.templating' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_TemplatingService()
    {
        $this->services['monolog.logger.templating'] = $instance = new \Symfony\Bridge\Monolog\Logger('templating');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'monolog.logger.translation' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Monolog\Logger A Symfony\Bridge\Monolog\Logger instance.
     */
    protected function getMonolog_Logger_TranslationService()
    {
        $this->services['monolog.logger.translation'] = $instance = new \Symfony\Bridge\Monolog\Logger('translation');

        $instance->pushHandler($this->get('monolog.handler.console'));
        $instance->pushHandler($this->get('monolog.handler.main'));
        $instance->pushHandler($this->get('monolog.handler.debug'));

        return $instance;
    }

    /**
     * Gets the 'ob_highcharts.twig.highcharts_extension' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Ob\HighchartsBundle\Twig\HighchartsExtension A Ob\HighchartsBundle\Twig\HighchartsExtension instance.
     */
    protected function getObHighcharts_Twig_HighchartsExtensionService()
    {
        return $this->services['ob_highcharts.twig.highcharts_extension'] = new \Ob\HighchartsBundle\Twig\HighchartsExtension();
    }

    /**
     * Gets the 'phpexcel' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Liuggio\ExcelBundle\Factory A Liuggio\ExcelBundle\Factory instance.
     */
    protected function getPhpexcelService()
    {
        return $this->services['phpexcel'] = new \Liuggio\ExcelBundle\Factory();
    }

    /**
     * Gets the 'profiler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\Profiler\Profiler A Symfony\Component\HttpKernel\Profiler\Profiler instance.
     */
    protected function getProfilerService()
    {
        $a = $this->get('monolog.logger.profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        $b = $this->get('kernel', ContainerInterface::NULL_ON_INVALID_REFERENCE);

        $c = new \Symfony\Component\HttpKernel\DataCollector\ConfigDataCollector();
        if ($this->has('kernel')) {
            $c->setKernel($b);
        }

        $this->services['profiler'] = $instance = new \Symfony\Component\HttpKernel\Profiler\Profiler(new \Symfony\Component\HttpKernel\Profiler\FileProfilerStorage(('file:'.__DIR__.'/profiler'), '', '', 86400), $a);

        $instance->add($this->get('data_collector.request'));
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\TimeDataCollector($b, $this->get('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE)));
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\MemoryDataCollector());
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\AjaxDataCollector());
        $instance->add($this->get('data_collector.form'));
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector());
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\LoggerDataCollector($a));
        $instance->add(new \Symfony\Component\HttpKernel\DataCollector\EventDataCollector($this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE)));
        $instance->add($this->get('data_collector.router'));
        $instance->add($this->get('data_collector.translation'));
        $instance->add(new \Symfony\Bundle\SecurityBundle\DataCollector\SecurityDataCollector($this->get('security.token_storage', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('security.role_hierarchy'), $this->get('security.logout_url_generator')));
        $instance->add(new \Symfony\Bridge\Twig\DataCollector\TwigDataCollector($this->get('twig.profile')));
        $instance->add($this->get('data_collector.dump'));
        $instance->add(new \Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector($this));
        $instance->add($this->get('doctrine_mongodb.odm.data_collector.pretty'));
        $instance->add(new \Misd\GuzzleBundle\DataCollector\GuzzleDataCollector($this->get('misd_guzzle.log.adapter.array')));
        $instance->add($this->get('memcache.data_collector'));
        $instance->add($c);

        return $instance;
    }

    /**
     * Gets the 'profiler_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ProfilerListener A Symfony\Component\HttpKernel\EventListener\ProfilerListener instance.
     */
    protected function getProfilerListenerService()
    {
        return $this->services['profiler_listener'] = new \Symfony\Component\HttpKernel\EventListener\ProfilerListener($this->get('profiler'), $this->get('request_stack'), NULL, false, false);
    }

    /**
     * Gets the 'property_accessor' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor A Symfony\Component\PropertyAccess\PropertyAccessor instance.
     */
    protected function getPropertyAccessorService()
    {
        return $this->services['property_accessor'] = new \Symfony\Component\PropertyAccess\PropertyAccessor(false, false);
    }

    /**
     * Gets the 'request' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @throws RuntimeException always since this service is expected to be injected dynamically
     * @throws InactiveScopeException when the 'request' service is requested while the 'request' scope is not active
     * @deprecated The "request" service is deprecated since Symfony 2.7 and will be removed in 3.0. Use the "request_stack" service instead.
     */
    protected function getRequestService()
    {
        if (!isset($this->scopedServices['request'])) {
            throw new InactiveScopeException('request', 'request');
        }

        throw new RuntimeException('You have requested a synthetic service ("request"). The DIC does not know how to construct this service.');
    }

    /**
     * Gets the 'request_stack' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestStack A Symfony\Component\HttpFoundation\RequestStack instance.
     */
    protected function getRequestStackService()
    {
        return $this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack();
    }

    /**
     * Gets the 'response_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ResponseListener A Symfony\Component\HttpKernel\EventListener\ResponseListener instance.
     */
    protected function getResponseListenerService()
    {
        return $this->services['response_listener'] = new \Symfony\Component\HttpKernel\EventListener\ResponseListener('UTF-8');
    }

    /**
     * Gets the 'router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router A Symfony\Bundle\FrameworkBundle\Routing\Router instance.
     */
    protected function getRouterService()
    {
        $this->services['router'] = $instance = new \Symfony\Bundle\FrameworkBundle\Routing\Router($this, ($this->targetDirs[2].'/config/routing_dev.yml'), array('cache_dir' => __DIR__, 'debug' => true, 'generator_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator', 'generator_base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator', 'generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper', 'generator_cache_class' => 'appDevUrlGenerator', 'matcher_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher', 'matcher_base_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher', 'matcher_dumper_class' => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper', 'matcher_cache_class' => 'appDevUrlMatcher', 'strict_requirements' => false), $this->get('router.request_context', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('monolog.logger.router', ContainerInterface::NULL_ON_INVALID_REFERENCE));

        $instance->setConfigCacheFactory($this->get('config_cache_factory'));

        return $instance;
    }

    /**
     * Gets the 'router_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\RouterListener A Symfony\Component\HttpKernel\EventListener\RouterListener instance.
     */
    protected function getRouterListenerService()
    {
        return $this->services['router_listener'] = new \Symfony\Component\HttpKernel\EventListener\RouterListener($this->get('router'), $this->get('request_stack'), $this->get('router.request_context', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('monolog.logger.request', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'routing.loader' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader A Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader instance.
     */
    protected function getRouting_LoaderService()
    {
        $a = $this->get('file_locator');
        $b = $this->get('annotation_reader');

        $c = new \Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader($b);

        $d = new \Symfony\Component\Config\Loader\LoaderResolver();
        $d->addLoader(new \Symfony\Component\Routing\Loader\XmlFileLoader($a));
        $d->addLoader(new \Symfony\Component\Routing\Loader\YamlFileLoader($a));
        $d->addLoader(new \Symfony\Component\Routing\Loader\PhpFileLoader($a));
        $d->addLoader(new \Symfony\Component\Routing\Loader\DirectoryLoader($a));
        $d->addLoader(new \Symfony\Component\Routing\Loader\DependencyInjection\ServiceRouterLoader($this));
        $d->addLoader(new \Symfony\Component\Routing\Loader\AnnotationDirectoryLoader($a, $c));
        $d->addLoader(new \Symfony\Component\Routing\Loader\AnnotationFileLoader($a, $c));
        $d->addLoader($c);

        return $this->services['routing.loader'] = new \Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader($this->get('controller_name_converter'), $d);
    }

    /**
     * Gets the 'security.acl.provider' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \IamPersistent\MongoDBAclBundle\Security\Acl\MutableAclProvider A IamPersistent\MongoDBAclBundle\Security\Acl\MutableAclProvider instance.
     */
    protected function getSecurity_Acl_ProviderService()
    {
        return $this->services['security.acl.provider'] = new \IamPersistent\MongoDBAclBundle\Security\Acl\MutableAclProvider($this->get('doctrine_mongodb.odm.default_connection'), 'mbh', new \Symfony\Component\Security\Acl\Domain\PermissionGrantingStrategy(), array('entry_collection' => 'acl_entry', 'oid_collection' => 'acl_oid'), NULL);
    }

    /**
     * Gets the 'security.authentication.guard_handler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Guard\GuardAuthenticatorHandler A Symfony\Component\Security\Guard\GuardAuthenticatorHandler instance.
     */
    protected function getSecurity_Authentication_GuardHandlerService()
    {
        return $this->services['security.authentication.guard_handler'] = new \Symfony\Component\Security\Guard\GuardAuthenticatorHandler($this->get('security.token_storage'), $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'security.authentication.success_handler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\UserBundle\Security\AuthenticationSuccessHandler A MBH\Bundle\UserBundle\Security\AuthenticationSuccessHandler instance.
     */
    protected function getSecurity_Authentication_SuccessHandlerService()
    {
        return $this->services['security.authentication.success_handler'] = new \MBH\Bundle\UserBundle\Security\AuthenticationSuccessHandler($this->get('security.http_utils'), array(), $this->get('mbh.mbhs'));
    }

    /**
     * Gets the 'security.authentication.success_handler.main.form_login' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \MBH\Bundle\UserBundle\Security\AuthenticationSuccessHandler A MBH\Bundle\UserBundle\Security\AuthenticationSuccessHandler instance.
     */
    protected function getSecurity_Authentication_SuccessHandler_Main_FormLoginService()
    {
        $this->services['security.authentication.success_handler.main.form_login'] = $instance = new \MBH\Bundle\UserBundle\Security\AuthenticationSuccessHandler($this->get('security.http_utils'), array(), $this->get('mbh.mbhs'));

        $instance->setOptions(array('always_use_default_target_path' => false, 'default_target_path' => '/', 'use_referer' => true, 'login_path' => '/user/login', 'target_path_parameter' => '_target_path'));
        $instance->setProviderKey('main');

        return $instance;
    }

    /**
     * Gets the 'security.authentication_utils' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Http\Authentication\AuthenticationUtils A Symfony\Component\Security\Http\Authentication\AuthenticationUtils instance.
     */
    protected function getSecurity_AuthenticationUtilsService()
    {
        return $this->services['security.authentication_utils'] = new \Symfony\Component\Security\Http\Authentication\AuthenticationUtils($this->get('request_stack'));
    }

    /**
     * Gets the 'security.authorization_checker' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Core\Authorization\AuthorizationChecker A Symfony\Component\Security\Core\Authorization\AuthorizationChecker instance.
     */
    protected function getSecurity_AuthorizationCheckerService()
    {
        return $this->services['security.authorization_checker'] = new \Symfony\Component\Security\Core\Authorization\AuthorizationChecker($this->get('security.token_storage'), $this->get('security.authentication.manager'), $this->get('security.access.decision_manager'), false);
    }

    /**
     * Gets the 'security.context' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Core\SecurityContext A Symfony\Component\Security\Core\SecurityContext instance.
     *
     * @deprecated The "security.context" service is deprecated since Symfony 2.6 and will be removed in 3.0.
     */
    protected function getSecurity_ContextService()
    {
        @trigger_error('The "security.context" service is deprecated since Symfony 2.6 and will be removed in 3.0.', E_USER_DEPRECATED);

        return $this->services['security.context'] = new \Symfony\Component\Security\Core\SecurityContext($this->get('security.token_storage'), $this->get('security.authorization_checker'));
    }

    /**
     * Gets the 'security.csrf.token_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Csrf\CsrfTokenManager A Symfony\Component\Security\Csrf\CsrfTokenManager instance.
     */
    protected function getSecurity_Csrf_TokenManagerService()
    {
        return $this->services['security.csrf.token_manager'] = new \Symfony\Component\Security\Csrf\CsrfTokenManager(new \Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator(), new \Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage($this->get('session')));
    }

    /**
     * Gets the 'security.encoder_factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Core\Encoder\EncoderFactory A Symfony\Component\Security\Core\Encoder\EncoderFactory instance.
     */
    protected function getSecurity_EncoderFactoryService()
    {
        return $this->services['security.encoder_factory'] = new \Symfony\Component\Security\Core\Encoder\EncoderFactory(array('FOS\\UserBundle\\Model\\UserInterface' => array('class' => 'Symfony\\Component\\Security\\Core\\Encoder\\MessageDigestPasswordEncoder', 'arguments' => array(0 => 'sha512', 1 => true, 2 => 5000))));
    }

    /**
     * Gets the 'security.firewall' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Http\Firewall A Symfony\Component\Security\Http\Firewall instance.
     */
    protected function getSecurity_FirewallService()
    {
        return $this->services['security.firewall'] = new \Symfony\Component\Security\Http\Firewall(new \Symfony\Bundle\SecurityBundle\Security\FirewallMap($this, array('security.firewall.map.context.dev' => new \Symfony\Component\HttpFoundation\RequestMatcher('^/(_profiler|_wdt|css|js)'), 'security.firewall.map.context.main' => new \Symfony\Component\HttpFoundation\RequestMatcher('^/'))), $this->get('debug.event_dispatcher'));
    }

    /**
     * Gets the 'security.firewall.map.context.dev' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Security\FirewallContext A Symfony\Bundle\SecurityBundle\Security\FirewallContext instance.
     */
    protected function getSecurity_Firewall_Map_Context_DevService()
    {
        return $this->services['security.firewall.map.context.dev'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallContext(array(), NULL);
    }

    /**
     * Gets the 'security.firewall.map.context.main' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Security\FirewallContext A Symfony\Bundle\SecurityBundle\Security\FirewallContext instance.
     */
    protected function getSecurity_Firewall_Map_Context_MainService()
    {
        $a = $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        $b = $this->get('security.token_storage');
        $c = $this->get('fos_user.user_provider.username_email');
        $d = $this->get('debug.event_dispatcher', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        $e = $this->get('security.http_utils');
        $f = $this->get('http_kernel');
        $g = $this->get('security.authentication.manager');
        $h = $this->get('security.authentication.session_strategy');

        $i = new \Symfony\Component\HttpFoundation\RequestMatcher('^/management/channelmanager/package/notifications');

        $j = new \Symfony\Component\HttpFoundation\RequestMatcher('^/management/online/api');

        $k = new \Symfony\Component\HttpFoundation\RequestMatcher('^/media/cache/resolve');

        $l = new \Symfony\Component\HttpFoundation\RequestMatcher('^/user/login$');

        $m = new \Symfony\Component\HttpFoundation\RequestMatcher('^/_wdt');

        $n = new \Symfony\Component\HttpFoundation\RequestMatcher('^/_profiler');

        $o = new \Symfony\Component\HttpFoundation\RequestMatcher('^/');

        $p = new \Symfony\Component\Security\Http\AccessMap();
        $p->add($i, array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $p->add($j, array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $p->add($k, array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $p->add($l, array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $p->add($m, array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $p->add($n, array(0 => 'IS_AUTHENTICATED_ANONYMOUSLY'), NULL);
        $p->add($o, array(0 => 'ROLE_BASE_USER'), NULL);

        $q = new \Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices(array(0 => $c), 'mySyperSecretKeyForSymfony', 'main', array('lifetime' => 3600, 'path' => '/', 'domain' => NULL, 'name' => 'REMEMBERME', 'secure' => false, 'httponly' => true, 'always_remember_me' => false, 'remember_me_parameter' => '_remember_me'), $a);

        $r = new \Symfony\Component\Security\Http\Firewall\LogoutListener($b, $e, new \Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler($e, '/'), array('csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'logout', 'logout_path' => '/user/logout'));
        $r->addHandler(new \Symfony\Component\Security\Http\Logout\SessionLogoutHandler());
        $r->addHandler($q);

        $s = new \Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler($f, $e, array(), $a);
        $s->setOptions(array('login_path' => '/user/login', 'failure_path' => NULL, 'failure_forward' => false, 'failure_path_parameter' => '_failure_path'));

        $t = new \Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener($b, $g, $h, $e, 'main', $this->get('security.authentication.success_handler.main.form_login'), $s, array('check_path' => '/user/login_check', 'require_previous_session' => true, 'use_forward' => false, 'username_parameter' => '_username', 'password_parameter' => '_password', 'csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'authenticate', 'post_only' => true), $a, $d, $this->get('form.csrf_provider'));
        $t->setRememberMeServices($q);

        return $this->services['security.firewall.map.context.main'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallContext(array(0 => new \Symfony\Component\Security\Http\Firewall\ChannelListener($p, new \Symfony\Component\Security\Http\EntryPoint\RetryAuthenticationEntryPoint(80, 443), $a), 1 => new \Symfony\Component\Security\Http\Firewall\ContextListener($b, array(0 => $c), 'main', $a, $d), 2 => $r, 3 => $t, 4 => new \Symfony\Component\Security\Http\Firewall\RememberMeListener($b, $q, $g, $a, $d, true, $h), 5 => new \Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener($b, '578dd7d7a6f396.63738139', $a, $g), 6 => new \Symfony\Component\Security\Http\Firewall\AccessListener($b, $this->get('security.access.decision_manager'), $p, $g)), new \Symfony\Component\Security\Http\Firewall\ExceptionListener($b, $this->get('security.authentication.trust_resolver'), $e, 'main', new \Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint($f, $e, '/user/login', false), NULL, NULL, $a, false));
    }

    /**
     * Gets the 'security.password_encoder' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Core\Encoder\UserPasswordEncoder A Symfony\Component\Security\Core\Encoder\UserPasswordEncoder instance.
     */
    protected function getSecurity_PasswordEncoderService()
    {
        return $this->services['security.password_encoder'] = new \Symfony\Component\Security\Core\Encoder\UserPasswordEncoder($this->get('security.encoder_factory'));
    }

    /**
     * Gets the 'security.rememberme.response_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Http\RememberMe\ResponseListener A Symfony\Component\Security\Http\RememberMe\ResponseListener instance.
     */
    protected function getSecurity_Rememberme_ResponseListenerService()
    {
        return $this->services['security.rememberme.response_listener'] = new \Symfony\Component\Security\Http\RememberMe\ResponseListener();
    }

    /**
     * Gets the 'security.secure_random' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Core\Util\SecureRandom A Symfony\Component\Security\Core\Util\SecureRandom instance.
     *
     * @deprecated The "security.secure_random" service is deprecated since Symfony 2.8 and will be removed in 3.0. Use the random_bytes() function instead.
     */
    protected function getSecurity_SecureRandomService()
    {
        @trigger_error('The "security.secure_random" service is deprecated since Symfony 2.8 and will be removed in 3.0. Use the random_bytes() function instead.', E_USER_DEPRECATED);

        return $this->services['security.secure_random'] = new \Symfony\Component\Security\Core\Util\SecureRandom((__DIR__.'/secure_random.seed'), $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'security.token_storage' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage A Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage instance.
     */
    protected function getSecurity_TokenStorageService()
    {
        return $this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage();
    }

    /**
     * Gets the 'security.user_checker.main' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Core\User\UserChecker A Symfony\Component\Security\Core\User\UserChecker instance.
     */
    protected function getSecurity_UserChecker_MainService()
    {
        return $this->services['security.user_checker.main'] = new \Symfony\Component\Security\Core\User\UserChecker();
    }

    /**
     * Gets the 'security.validator.user_password' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator A Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator instance.
     */
    protected function getSecurity_Validator_UserPasswordService()
    {
        return $this->services['security.validator.user_password'] = new \Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator($this->get('security.token_storage'), $this->get('security.encoder_factory'));
    }

    /**
     * Gets the 'sensio_distribution.security_checker' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \SensioLabs\Security\SecurityChecker A SensioLabs\Security\SecurityChecker instance.
     */
    protected function getSensioDistribution_SecurityCheckerService()
    {
        return $this->services['sensio_distribution.security_checker'] = new \SensioLabs\Security\SecurityChecker();
    }

    /**
     * Gets the 'sensio_distribution.security_checker.command' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \SensioLabs\Security\Command\SecurityCheckerCommand A SensioLabs\Security\Command\SecurityCheckerCommand instance.
     */
    protected function getSensioDistribution_SecurityChecker_CommandService()
    {
        return $this->services['sensio_distribution.security_checker.command'] = new \SensioLabs\Security\Command\SecurityCheckerCommand($this->get('sensio_distribution.security_checker'));
    }

    /**
     * Gets the 'sensio_distribution.webconfigurator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\DistributionBundle\Configurator\Configurator A Sensio\Bundle\DistributionBundle\Configurator\Configurator instance.
     */
    protected function getSensioDistribution_WebconfiguratorService()
    {
        $this->services['sensio_distribution.webconfigurator'] = $instance = new \Sensio\Bundle\DistributionBundle\Configurator\Configurator($this->targetDirs[2]);

        $instance->addStep(new \Sensio\Bundle\DistributionBundle\Configurator\Step\DoctrineStep(), 10);
        $instance->addStep(new \Sensio\Bundle\DistributionBundle\Configurator\Step\SecretStep(), 0);

        return $instance;
    }

    /**
     * Gets the 'sensio_framework_extra.cache.listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\HttpCacheListener A Sensio\Bundle\FrameworkExtraBundle\EventListener\HttpCacheListener instance.
     */
    protected function getSensioFrameworkExtra_Cache_ListenerService()
    {
        return $this->services['sensio_framework_extra.cache.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\HttpCacheListener();
    }

    /**
     * Gets the 'sensio_framework_extra.controller.listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener A Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener instance.
     */
    protected function getSensioFrameworkExtra_Controller_ListenerService()
    {
        return $this->services['sensio_framework_extra.controller.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener($this->get('annotation_reader'));
    }

    /**
     * Gets the 'sensio_framework_extra.converter.datetime' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter A Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter instance.
     */
    protected function getSensioFrameworkExtra_Converter_DatetimeService()
    {
        return $this->services['sensio_framework_extra.converter.datetime'] = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter();
    }

    /**
     * Gets the 'sensio_framework_extra.converter.doctrine.orm' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter A Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter instance.
     */
    protected function getSensioFrameworkExtra_Converter_Doctrine_OrmService()
    {
        return $this->services['sensio_framework_extra.converter.doctrine.orm'] = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter(NULL);
    }

    /**
     * Gets the 'sensio_framework_extra.converter.listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener A Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener instance.
     */
    protected function getSensioFrameworkExtra_Converter_ListenerService()
    {
        return $this->services['sensio_framework_extra.converter.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener($this->get('sensio_framework_extra.converter.manager'), true);
    }

    /**
     * Gets the 'sensio_framework_extra.converter.manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager A Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager instance.
     */
    protected function getSensioFrameworkExtra_Converter_ManagerService()
    {
        $this->services['sensio_framework_extra.converter.manager'] = $instance = new \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager();

        $instance->add($this->get('sensio_framework_extra.converter.doctrine.orm'), 0, 'doctrine.orm');
        $instance->add($this->get('sensio_framework_extra.converter.datetime'), 0, 'datetime');
        $instance->add($this->get('misd_guzzle.param_converter'), -100, 'guzzle');
        $instance->add($this->get('doctrine_mongo_db_param_converter'), 0, 'doctrine.odm');

        return $instance;
    }

    /**
     * Gets the 'sensio_framework_extra.security.listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener A Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener instance.
     */
    protected function getSensioFrameworkExtra_Security_ListenerService()
    {
        return $this->services['sensio_framework_extra.security.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener(NULL, new \Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage(), $this->get('security.authentication.trust_resolver', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('security.role_hierarchy', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('security.token_storage', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('security.authorization_checker', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'sensio_framework_extra.view.guesser' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser A Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser instance.
     */
    protected function getSensioFrameworkExtra_View_GuesserService()
    {
        return $this->services['sensio_framework_extra.view.guesser'] = new \Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser($this->get('kernel'));
    }

    /**
     * Gets the 'sensio_framework_extra.view.listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener A Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener instance.
     */
    protected function getSensioFrameworkExtra_View_ListenerService()
    {
        return $this->services['sensio_framework_extra.view.listener'] = new \Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener($this);
    }

    /**
     * Gets the 'serializer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Serializer\Serializer A Symfony\Component\Serializer\Serializer instance.
     */
    protected function getSerializerService()
    {
        return $this->services['serializer'] = new \Symfony\Component\Serializer\Serializer(array(0 => $this->get('mbh.get_set_method_normalizer'), 1 => new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer(new \Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory(new \Symfony\Component\Serializer\Mapping\Loader\LoaderChain(array()), NULL), NULL, $this->get('property_accessor'))), array(0 => new \Symfony\Component\Serializer\Encoder\XmlEncoder(), 1 => new \Symfony\Component\Serializer\Encoder\JsonEncoder()));
    }

    /**
     * Gets the 'service_container' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @throws RuntimeException always since this service is expected to be injected dynamically
     */
    protected function getServiceContainerService()
    {
        throw new RuntimeException('You have requested a synthetic service ("service_container"). The DIC does not know how to construct this service.');
    }

    /**
     * Gets the 'session' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session A Symfony\Component\HttpFoundation\Session\Session instance.
     */
    protected function getSessionService()
    {
        return $this->services['session'] = new \Symfony\Component\HttpFoundation\Session\Session($this->get('session.storage.native'), new \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag(), new \Symfony\Component\HttpFoundation\Session\Flash\FlashBag());
    }

    /**
     * Gets the 'session.save_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\SaveSessionListener A Symfony\Component\HttpKernel\EventListener\SaveSessionListener instance.
     */
    protected function getSession_SaveListenerService()
    {
        return $this->services['session.save_listener'] = new \Symfony\Component\HttpKernel\EventListener\SaveSessionListener();
    }

    /**
     * Gets the 'session.storage.filesystem' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage A Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage instance.
     */
    protected function getSession_Storage_FilesystemService()
    {
        return $this->services['session.storage.filesystem'] = new \Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage((__DIR__.'/sessions'), 'MOCKSESSID', $this->get('session.storage.metadata_bag'));
    }

    /**
     * Gets the 'session.storage.native' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage A Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage instance.
     */
    protected function getSession_Storage_NativeService()
    {
        return $this->services['session.storage.native'] = new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage(array('cookie_httponly' => true, 'gc_probability' => 1), NULL, $this->get('session.storage.metadata_bag'));
    }

    /**
     * Gets the 'session.storage.php_bridge' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage A Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage instance.
     */
    protected function getSession_Storage_PhpBridgeService()
    {
        return $this->services['session.storage.php_bridge'] = new \Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage(NULL, $this->get('session.storage.metadata_bag'));
    }

    /**
     * Gets the 'session_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\EventListener\SessionListener A Symfony\Bundle\FrameworkBundle\EventListener\SessionListener instance.
     */
    protected function getSessionListenerService()
    {
        return $this->services['session_listener'] = new \Symfony\Bundle\FrameworkBundle\EventListener\SessionListener($this);
    }

    /**
     * Gets the 'stof_doctrine_extensions.event_listener.blame' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Stof\DoctrineExtensionsBundle\EventListener\BlameListener A Stof\DoctrineExtensionsBundle\EventListener\BlameListener instance.
     */
    protected function getStofDoctrineExtensions_EventListener_BlameService()
    {
        return $this->services['stof_doctrine_extensions.event_listener.blame'] = new \Stof\DoctrineExtensionsBundle\EventListener\BlameListener($this->get('stof_doctrine_extensions.listener.blameable'), $this->get('security.token_storage'), $this->get('security.authorization_checker'));
    }

    /**
     * Gets the 'stof_doctrine_extensions.event_listener.locale' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Stof\DoctrineExtensionsBundle\EventListener\LocaleListener A Stof\DoctrineExtensionsBundle\EventListener\LocaleListener instance.
     */
    protected function getStofDoctrineExtensions_EventListener_LocaleService()
    {
        return $this->services['stof_doctrine_extensions.event_listener.locale'] = new \Stof\DoctrineExtensionsBundle\EventListener\LocaleListener($this->get('stof_doctrine_extensions.listener.translatable'));
    }

    /**
     * Gets the 'stof_doctrine_extensions.event_listener.logger' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Stof\DoctrineExtensionsBundle\EventListener\LoggerListener A Stof\DoctrineExtensionsBundle\EventListener\LoggerListener instance.
     */
    protected function getStofDoctrineExtensions_EventListener_LoggerService()
    {
        return $this->services['stof_doctrine_extensions.event_listener.logger'] = new \Stof\DoctrineExtensionsBundle\EventListener\LoggerListener($this->get('stof_doctrine_extensions.listener.loggable'), $this->get('security.token_storage'), $this->get('security.authorization_checker'));
    }

    /**
     * Gets the 'stof_doctrine_extensions.uploadable.manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager A Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager instance.
     */
    protected function getStofDoctrineExtensions_Uploadable_ManagerService()
    {
        $a = new \Gedmo\Uploadable\UploadableListener(new \Stof\DoctrineExtensionsBundle\Uploadable\MimeTypeGuesserAdapter());
        $a->setAnnotationReader($this->get('annotation_reader'));
        $a->setDefaultFileInfoClass('Stof\\DoctrineExtensionsBundle\\Uploadable\\UploadedFileInfo');

        return $this->services['stof_doctrine_extensions.uploadable.manager'] = new \Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager($a, 'Stof\\DoctrineExtensionsBundle\\Uploadable\\UploadedFileInfo');
    }

    /**
     * Gets the 'streamed_response_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\StreamedResponseListener A Symfony\Component\HttpKernel\EventListener\StreamedResponseListener instance.
     */
    protected function getStreamedResponseListenerService()
    {
        return $this->services['streamed_response_listener'] = new \Symfony\Component\HttpKernel\EventListener\StreamedResponseListener();
    }

    /**
     * Gets the 'swiftmailer.email_sender.listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\SwiftmailerBundle\EventListener\EmailSenderListener A Symfony\Bundle\SwiftmailerBundle\EventListener\EmailSenderListener instance.
     */
    protected function getSwiftmailer_EmailSender_ListenerService()
    {
        return $this->services['swiftmailer.email_sender.listener'] = new \Symfony\Bundle\SwiftmailerBundle\EventListener\EmailSenderListener($this, $this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'swiftmailer.mailer.default' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Swift_Mailer A Swift_Mailer instance.
     */
    protected function getSwiftmailer_Mailer_DefaultService()
    {
        return $this->services['swiftmailer.mailer.default'] = new \Swift_Mailer($this->get('swiftmailer.mailer.default.transport'));
    }

    /**
     * Gets the 'swiftmailer.mailer.default.plugin.messagelogger' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Swift_Plugins_MessageLogger A Swift_Plugins_MessageLogger instance.
     */
    protected function getSwiftmailer_Mailer_Default_Plugin_MessageloggerService()
    {
        return $this->services['swiftmailer.mailer.default.plugin.messagelogger'] = new \Swift_Plugins_MessageLogger();
    }

    /**
     * Gets the 'swiftmailer.mailer.default.spool' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Swift_MemorySpool A Swift_MemorySpool instance.
     */
    protected function getSwiftmailer_Mailer_Default_SpoolService()
    {
        return $this->services['swiftmailer.mailer.default.spool'] = new \Swift_MemorySpool();
    }

    /**
     * Gets the 'swiftmailer.mailer.default.transport' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Swift_Transport_SpoolTransport A Swift_Transport_SpoolTransport instance.
     */
    protected function getSwiftmailer_Mailer_Default_TransportService()
    {
        $this->services['swiftmailer.mailer.default.transport'] = $instance = new \Swift_Transport_SpoolTransport($this->get('swiftmailer.mailer.default.transport.eventdispatcher'), $this->get('swiftmailer.mailer.default.spool'));

        $instance->registerPlugin($this->get('swiftmailer.mailer.default.plugin.messagelogger'));

        return $instance;
    }

    /**
     * Gets the 'swiftmailer.mailer.default.transport.real' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Swift_Transport_EsmtpTransport A Swift_Transport_EsmtpTransport instance.
     */
    protected function getSwiftmailer_Mailer_Default_Transport_RealService()
    {
        $a = new \Swift_Transport_Esmtp_AuthHandler(array(0 => new \Swift_Transport_Esmtp_Auth_CramMd5Authenticator(), 1 => new \Swift_Transport_Esmtp_Auth_LoginAuthenticator(), 2 => new \Swift_Transport_Esmtp_Auth_PlainAuthenticator()));
        $a->setUsername('robot@maxi-booking.ru');
        $a->setPassword('ghjlflbv10rjgbq');
        $a->setAuthMode(NULL);

        $this->services['swiftmailer.mailer.default.transport.real'] = $instance = new \Swift_Transport_EsmtpTransport(new \Swift_Transport_StreamBuffer(new \Swift_StreamFilters_StringReplacementFilterFactory()), array(0 => $a), $this->get('swiftmailer.mailer.default.transport.eventdispatcher'));

        $instance->setHost('smtp.yandex.ru');
        $instance->setPort(465);
        $instance->setEncryption('ssl');
        $instance->setTimeout(30);
        $instance->setSourceIp(NULL);

        return $instance;
    }

    /**
     * Gets the 'templating' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\TwigBundle\TwigEngine A Symfony\Bundle\TwigBundle\TwigEngine instance.
     */
    protected function getTemplatingService()
    {
        return $this->services['templating'] = new \Symfony\Bundle\TwigBundle\TwigEngine($this->get('twig'), $this->get('templating.name_parser'), $this->get('templating.locator'));
    }

    /**
     * Gets the 'templating.filename_parser' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\TemplateFilenameParser A Symfony\Bundle\FrameworkBundle\Templating\TemplateFilenameParser instance.
     */
    protected function getTemplating_FilenameParserService()
    {
        return $this->services['templating.filename_parser'] = new \Symfony\Bundle\FrameworkBundle\Templating\TemplateFilenameParser();
    }

    /**
     * Gets the 'templating.helper.assets' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper A Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper instance.
     */
    protected function getTemplating_Helper_AssetsService()
    {
        return $this->services['templating.helper.assets'] = new \Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper($this->get('assets.packages'), array());
    }

    /**
     * Gets the 'templating.helper.gravatar' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Ornicar\GravatarBundle\Templating\Helper\GravatarHelper A Ornicar\GravatarBundle\Templating\Helper\GravatarHelper instance.
     */
    protected function getTemplating_Helper_GravatarService()
    {
        return $this->services['templating.helper.gravatar'] = new \Ornicar\GravatarBundle\Templating\Helper\GravatarHelper($this->get('gravatar.api'), $this);
    }

    /**
     * Gets the 'templating.helper.logout_url' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Templating\Helper\LogoutUrlHelper A Symfony\Bundle\SecurityBundle\Templating\Helper\LogoutUrlHelper instance.
     */
    protected function getTemplating_Helper_LogoutUrlService()
    {
        return $this->services['templating.helper.logout_url'] = new \Symfony\Bundle\SecurityBundle\Templating\Helper\LogoutUrlHelper($this->get('security.logout_url_generator'));
    }

    /**
     * Gets the 'templating.helper.router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper A Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper instance.
     */
    protected function getTemplating_Helper_RouterService()
    {
        return $this->services['templating.helper.router'] = new \Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper($this->get('router'));
    }

    /**
     * Gets the 'templating.helper.security' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\SecurityBundle\Templating\Helper\SecurityHelper A Symfony\Bundle\SecurityBundle\Templating\Helper\SecurityHelper instance.
     */
    protected function getTemplating_Helper_SecurityService()
    {
        return $this->services['templating.helper.security'] = new \Symfony\Bundle\SecurityBundle\Templating\Helper\SecurityHelper($this->get('security.authorization_checker', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'templating.loader' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\Loader\FilesystemLoader A Symfony\Bundle\FrameworkBundle\Templating\Loader\FilesystemLoader instance.
     */
    protected function getTemplating_LoaderService()
    {
        return $this->services['templating.loader'] = new \Symfony\Bundle\FrameworkBundle\Templating\Loader\FilesystemLoader($this->get('templating.locator'));
    }

    /**
     * Gets the 'templating.name_parser' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser A Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser instance.
     */
    protected function getTemplating_NameParserService()
    {
        return $this->services['templating.name_parser'] = new \Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser($this->get('kernel'));
    }

    /**
     * Gets the 'translation.dumper.csv' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Dumper\CsvFileDumper A Symfony\Component\Translation\Dumper\CsvFileDumper instance.
     */
    protected function getTranslation_Dumper_CsvService()
    {
        return $this->services['translation.dumper.csv'] = new \Symfony\Component\Translation\Dumper\CsvFileDumper();
    }

    /**
     * Gets the 'translation.dumper.ini' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Dumper\IniFileDumper A Symfony\Component\Translation\Dumper\IniFileDumper instance.
     */
    protected function getTranslation_Dumper_IniService()
    {
        return $this->services['translation.dumper.ini'] = new \Symfony\Component\Translation\Dumper\IniFileDumper();
    }

    /**
     * Gets the 'translation.dumper.json' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Dumper\JsonFileDumper A Symfony\Component\Translation\Dumper\JsonFileDumper instance.
     */
    protected function getTranslation_Dumper_JsonService()
    {
        return $this->services['translation.dumper.json'] = new \Symfony\Component\Translation\Dumper\JsonFileDumper();
    }

    /**
     * Gets the 'translation.dumper.mo' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Dumper\MoFileDumper A Symfony\Component\Translation\Dumper\MoFileDumper instance.
     */
    protected function getTranslation_Dumper_MoService()
    {
        return $this->services['translation.dumper.mo'] = new \Symfony\Component\Translation\Dumper\MoFileDumper();
    }

    /**
     * Gets the 'translation.dumper.php' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Dumper\PhpFileDumper A Symfony\Component\Translation\Dumper\PhpFileDumper instance.
     */
    protected function getTranslation_Dumper_PhpService()
    {
        return $this->services['translation.dumper.php'] = new \Symfony\Component\Translation\Dumper\PhpFileDumper();
    }

    /**
     * Gets the 'translation.dumper.po' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Dumper\PoFileDumper A Symfony\Component\Translation\Dumper\PoFileDumper instance.
     */
    protected function getTranslation_Dumper_PoService()
    {
        return $this->services['translation.dumper.po'] = new \Symfony\Component\Translation\Dumper\PoFileDumper();
    }

    /**
     * Gets the 'translation.dumper.qt' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Dumper\QtFileDumper A Symfony\Component\Translation\Dumper\QtFileDumper instance.
     */
    protected function getTranslation_Dumper_QtService()
    {
        return $this->services['translation.dumper.qt'] = new \Symfony\Component\Translation\Dumper\QtFileDumper();
    }

    /**
     * Gets the 'translation.dumper.res' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Dumper\IcuResFileDumper A Symfony\Component\Translation\Dumper\IcuResFileDumper instance.
     */
    protected function getTranslation_Dumper_ResService()
    {
        return $this->services['translation.dumper.res'] = new \Symfony\Component\Translation\Dumper\IcuResFileDumper();
    }

    /**
     * Gets the 'translation.dumper.xliff' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Dumper\XliffFileDumper A Symfony\Component\Translation\Dumper\XliffFileDumper instance.
     */
    protected function getTranslation_Dumper_XliffService()
    {
        return $this->services['translation.dumper.xliff'] = new \Symfony\Component\Translation\Dumper\XliffFileDumper();
    }

    /**
     * Gets the 'translation.dumper.yml' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Dumper\YamlFileDumper A Symfony\Component\Translation\Dumper\YamlFileDumper instance.
     */
    protected function getTranslation_Dumper_YmlService()
    {
        return $this->services['translation.dumper.yml'] = new \Symfony\Component\Translation\Dumper\YamlFileDumper();
    }

    /**
     * Gets the 'translation.extractor' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Extractor\ChainExtractor A Symfony\Component\Translation\Extractor\ChainExtractor instance.
     */
    protected function getTranslation_ExtractorService()
    {
        $this->services['translation.extractor'] = $instance = new \Symfony\Component\Translation\Extractor\ChainExtractor();

        $instance->addExtractor('php', $this->get('translation.extractor.php'));
        $instance->addExtractor('twig', $this->get('twig.translation.extractor'));

        return $instance;
    }

    /**
     * Gets the 'translation.extractor.php' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Translation\PhpExtractor A Symfony\Bundle\FrameworkBundle\Translation\PhpExtractor instance.
     */
    protected function getTranslation_Extractor_PhpService()
    {
        return $this->services['translation.extractor.php'] = new \Symfony\Bundle\FrameworkBundle\Translation\PhpExtractor();
    }

    /**
     * Gets the 'translation.loader' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader A Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader instance.
     */
    protected function getTranslation_LoaderService()
    {
        $a = $this->get('translation.loader.xliff');

        $this->services['translation.loader'] = $instance = new \Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader();

        $instance->addLoader('php', $this->get('translation.loader.php'));
        $instance->addLoader('yml', $this->get('translation.loader.yml'));
        $instance->addLoader('xlf', $a);
        $instance->addLoader('xliff', $a);
        $instance->addLoader('po', $this->get('translation.loader.po'));
        $instance->addLoader('mo', $this->get('translation.loader.mo'));
        $instance->addLoader('ts', $this->get('translation.loader.qt'));
        $instance->addLoader('csv', $this->get('translation.loader.csv'));
        $instance->addLoader('res', $this->get('translation.loader.res'));
        $instance->addLoader('dat', $this->get('translation.loader.dat'));
        $instance->addLoader('ini', $this->get('translation.loader.ini'));
        $instance->addLoader('json', $this->get('translation.loader.json'));

        return $instance;
    }

    /**
     * Gets the 'translation.loader.csv' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Loader\CsvFileLoader A Symfony\Component\Translation\Loader\CsvFileLoader instance.
     */
    protected function getTranslation_Loader_CsvService()
    {
        return $this->services['translation.loader.csv'] = new \Symfony\Component\Translation\Loader\CsvFileLoader();
    }

    /**
     * Gets the 'translation.loader.dat' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Loader\IcuDatFileLoader A Symfony\Component\Translation\Loader\IcuDatFileLoader instance.
     */
    protected function getTranslation_Loader_DatService()
    {
        return $this->services['translation.loader.dat'] = new \Symfony\Component\Translation\Loader\IcuDatFileLoader();
    }

    /**
     * Gets the 'translation.loader.ini' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Loader\IniFileLoader A Symfony\Component\Translation\Loader\IniFileLoader instance.
     */
    protected function getTranslation_Loader_IniService()
    {
        return $this->services['translation.loader.ini'] = new \Symfony\Component\Translation\Loader\IniFileLoader();
    }

    /**
     * Gets the 'translation.loader.json' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Loader\JsonFileLoader A Symfony\Component\Translation\Loader\JsonFileLoader instance.
     */
    protected function getTranslation_Loader_JsonService()
    {
        return $this->services['translation.loader.json'] = new \Symfony\Component\Translation\Loader\JsonFileLoader();
    }

    /**
     * Gets the 'translation.loader.mo' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Loader\MoFileLoader A Symfony\Component\Translation\Loader\MoFileLoader instance.
     */
    protected function getTranslation_Loader_MoService()
    {
        return $this->services['translation.loader.mo'] = new \Symfony\Component\Translation\Loader\MoFileLoader();
    }

    /**
     * Gets the 'translation.loader.php' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Loader\PhpFileLoader A Symfony\Component\Translation\Loader\PhpFileLoader instance.
     */
    protected function getTranslation_Loader_PhpService()
    {
        return $this->services['translation.loader.php'] = new \Symfony\Component\Translation\Loader\PhpFileLoader();
    }

    /**
     * Gets the 'translation.loader.po' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Loader\PoFileLoader A Symfony\Component\Translation\Loader\PoFileLoader instance.
     */
    protected function getTranslation_Loader_PoService()
    {
        return $this->services['translation.loader.po'] = new \Symfony\Component\Translation\Loader\PoFileLoader();
    }

    /**
     * Gets the 'translation.loader.qt' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Loader\QtFileLoader A Symfony\Component\Translation\Loader\QtFileLoader instance.
     */
    protected function getTranslation_Loader_QtService()
    {
        return $this->services['translation.loader.qt'] = new \Symfony\Component\Translation\Loader\QtFileLoader();
    }

    /**
     * Gets the 'translation.loader.res' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Loader\IcuResFileLoader A Symfony\Component\Translation\Loader\IcuResFileLoader instance.
     */
    protected function getTranslation_Loader_ResService()
    {
        return $this->services['translation.loader.res'] = new \Symfony\Component\Translation\Loader\IcuResFileLoader();
    }

    /**
     * Gets the 'translation.loader.xliff' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \JMS\TranslationBundle\Translation\Loader\Symfony\XliffLoader A JMS\TranslationBundle\Translation\Loader\Symfony\XliffLoader instance.
     */
    protected function getTranslation_Loader_XliffService()
    {
        return $this->services['translation.loader.xliff'] = new \JMS\TranslationBundle\Translation\Loader\Symfony\XliffLoader();
    }

    /**
     * Gets the 'translation.loader.yml' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Loader\YamlFileLoader A Symfony\Component\Translation\Loader\YamlFileLoader instance.
     */
    protected function getTranslation_Loader_YmlService()
    {
        return $this->services['translation.loader.yml'] = new \Symfony\Component\Translation\Loader\YamlFileLoader();
    }

    /**
     * Gets the 'translation.writer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\Writer\TranslationWriter A Symfony\Component\Translation\Writer\TranslationWriter instance.
     */
    protected function getTranslation_WriterService()
    {
        $this->services['translation.writer'] = $instance = new \Symfony\Component\Translation\Writer\TranslationWriter();

        $instance->addDumper('php', $this->get('translation.dumper.php'));
        $instance->addDumper('xlf', $this->get('translation.dumper.xliff'));
        $instance->addDumper('po', $this->get('translation.dumper.po'));
        $instance->addDumper('mo', $this->get('translation.dumper.mo'));
        $instance->addDumper('yml', $this->get('translation.dumper.yml'));
        $instance->addDumper('ts', $this->get('translation.dumper.qt'));
        $instance->addDumper('csv', $this->get('translation.dumper.csv'));
        $instance->addDumper('ini', $this->get('translation.dumper.ini'));
        $instance->addDumper('json', $this->get('translation.dumper.json'));
        $instance->addDumper('res', $this->get('translation.dumper.res'));

        return $instance;
    }

    /**
     * Gets the 'translator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Translation\DataCollectorTranslator A Symfony\Component\Translation\DataCollectorTranslator instance.
     */
    protected function getTranslatorService()
    {
        return $this->services['translator'] = new \Symfony\Component\Translation\DataCollectorTranslator(new \Symfony\Component\Translation\LoggingTranslator($this->get('translator.default'), $this->get('monolog.logger.translation')));
    }

    /**
     * Gets the 'translator.default' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Translation\Translator A Symfony\Bundle\FrameworkBundle\Translation\Translator instance.
     */
    protected function getTranslator_DefaultService()
    {
        $this->services['translator.default'] = $instance = new \Symfony\Bundle\FrameworkBundle\Translation\Translator($this, new \Symfony\Component\Translation\MessageSelector(), array('translation.loader.php' => array(0 => 'php'), 'translation.loader.yml' => array(0 => 'yml'), 'translation.loader.xliff' => array(0 => 'xlf', 1 => 'xliff'), 'translation.loader.po' => array(0 => 'po'), 'translation.loader.mo' => array(0 => 'mo'), 'translation.loader.qt' => array(0 => 'ts'), 'translation.loader.csv' => array(0 => 'csv'), 'translation.loader.res' => array(0 => 'res'), 'translation.loader.dat' => array(0 => 'dat'), 'translation.loader.ini' => array(0 => 'ini'), 'translation.loader.json' => array(0 => 'json')), array('cache_dir' => (__DIR__.'/translations'), 'debug' => true, 'resource_files' => array('ro' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.ro.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.ro.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.ro.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.ro.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.ro.yml')), 'vi' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.vi.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.vi.xlf'), 2 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.vi.yml'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.vi.yml')), 'ar' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.ar.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.ar.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.ar.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.ar.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.ar.yml')), 'bg' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.bg.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.bg.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.bg.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.bg.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.bg.yml')), 'pl' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.pl.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.pl.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.pl.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.pl.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.pl.yml')), 'fr' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.fr.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.fr.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.fr.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.fr.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.fr.yml')), 'th' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.th.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.th.xlf'), 2 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.th.yml'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.th.yml')), 'fa' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.fa.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.fa.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.fa.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.fa.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.fa.yml')), 'sr_Latn' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sr_Latn.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.sr_Latn.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.sr_Latn.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.sr_Latn.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.sr_Latn.yml')), 'sk' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sk.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.sk.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.sk.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.sk.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.sk.yml')), 'sv' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sv.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.sv.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.sv.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.sv.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.sv.yml')), 'zh_CN' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.zh_CN.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.zh_CN.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.zh_CN.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.zh_CN.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.zh_CN.yml')), 'fi' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.fi.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.fi.xlf'), 2 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.fi.yml'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.fi.yml')), 'gl' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.gl.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.gl.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.gl.xlf')), 'it' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.it.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.it.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.it.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.it.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.it.yml')), 'zh_TW' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.zh_TW.xlf')), 'el' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.el.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.el.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.el.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.el.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.el.yml')), 'pt' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.pt.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.pt.xlf'), 2 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.pt.yml'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.pt.yml')), 'eu' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.eu.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.eu.xlf'), 2 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.eu.yml'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.eu.yml')), 'tr' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.tr.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.tr.xlf'), 2 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.tr.yml'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.tr.yml')), 'hu' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.hu.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.hu.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.hu.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.hu.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.hu.yml')), 'es' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.es.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.es.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.es.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.es.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.es.yml')), 'ca' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.ca.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.ca.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.ca.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.ca.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.ca.yml')), 'de' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.de.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.de.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.de.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.de.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.de.yml')), 'sq' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sq.xlf')), 'he' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.he.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.he.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.he.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.he.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.he.yml')), 'en' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.en.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.en.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.en.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.en.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.en.yml'), 5 => ($this->targetDirs[3].'/src/MBH/Bundle/BaseBundle/Resources/translations/messages.en.yml'), 6 => ($this->targetDirs[3].'/src/MBH/Bundle/PackageBundle/Resources/translations/messages.en.yml'), 7 => ($this->targetDirs[3].'/src/MBH/Bundle/PackageBundle/Resources/translations/MBHPackageBundle.en.yml'), 8 => ($this->targetDirs[3].'/src/MBH/Bundle/OnlineBundle/Resources/translations/messages.en.yml'), 9 => ($this->targetDirs[3].'/src/MBH/Bundle/OnlineBundle/Resources/translations/MBHOnlineBundle.en.yml'), 10 => ($this->targetDirs[3].'/src/MBH/Bundle/VegaBundle/Resources/translations/messages.en.yml')), 'ru' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.ru.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.ru.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.ru.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.ru.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.ru.yml'), 5 => ($this->targetDirs[3].'/src/MBH/Bundle/BaseBundle/Resources/translations/validators.ru.yml'), 6 => ($this->targetDirs[3].'/src/MBH/Bundle/BaseBundle/Resources/translations/MBHBaseBundle.ru.yml'), 7 => ($this->targetDirs[3].'/src/MBH/Bundle/BaseBundle/Resources/translations/messages.ru.yml'), 8 => ($this->targetDirs[3].'/src/MBH/Bundle/BaseBundle/Resources/translations/individual.ru.yml'), 9 => ($this->targetDirs[3].'/src/MBH/Bundle/UserBundle/Resources/translations/MBHUserBundleRoles.ru.yml'), 10 => ($this->targetDirs[3].'/src/MBH/Bundle/UserBundle/Resources/translations/validators.ru.yml'), 11 => ($this->targetDirs[3].'/src/MBH/Bundle/UserBundle/Resources/translations/messages.ru.yml'), 12 => ($this->targetDirs[3].'/src/MBH/Bundle/UserBundle/Resources/translations/FOSUserBundle.ru.yml'), 13 => ($this->targetDirs[3].'/src/MBH/Bundle/HotelBundle/Resources/translations/validators.ru.yml'), 14 => ($this->targetDirs[3].'/src/MBH/Bundle/HotelBundle/Resources/translations/MBHHotelBundle.ru.yml'), 15 => ($this->targetDirs[3].'/src/MBH/Bundle/HotelBundle/Resources/translations/messages.ru.yml'), 16 => ($this->targetDirs[3].'/src/MBH/Bundle/PriceBundle/Resources/translations/validators.ru.yml'), 17 => ($this->targetDirs[3].'/src/MBH/Bundle/PriceBundle/Resources/translations/messages.ru.yml'), 18 => ($this->targetDirs[3].'/src/MBH/Bundle/PackageBundle/Resources/translations/MBHPackageBundle.ru.yml'), 19 => ($this->targetDirs[3].'/src/MBH/Bundle/PackageBundle/Resources/translations/validators.ru.yml'), 20 => ($this->targetDirs[3].'/src/MBH/Bundle/PackageBundle/Resources/translations/messages.ru.yml'), 21 => ($this->targetDirs[3].'/src/MBH/Bundle/CashBundle/Resources/translations/validators.ru.yml'), 22 => ($this->targetDirs[3].'/src/MBH/Bundle/CashBundle/Resources/translations/messages.ru.yml'), 23 => ($this->targetDirs[3].'/src/MBH/Bundle/CashBundle/Resources/translations/MBHCashBundle.ru.yml'), 24 => ($this->targetDirs[3].'/src/MBH/Bundle/ChannelManagerBundle/Resources/translations/validators.ru.yml'), 25 => ($this->targetDirs[3].'/src/MBH/Bundle/ChannelManagerBundle/Resources/translations/messages.ru.yml'), 26 => ($this->targetDirs[3].'/src/MBH/Bundle/ChannelManagerBundle/Resources/translations/MBHChannelManagerBundle.ru.yml'), 27 => ($this->targetDirs[3].'/src/MBH/Bundle/OnlineBundle/Resources/translations/validators.ru.yml'), 28 => ($this->targetDirs[3].'/src/MBH/Bundle/OnlineBundle/Resources/translations/messages.ru.yml'), 29 => ($this->targetDirs[3].'/src/MBH/Bundle/OnlineBundle/Resources/translations/MBHOnlineBundle.ru.yml'), 30 => ($this->targetDirs[3].'/src/MBH/Bundle/ClientBundle/Resources/translations/validators.ru.yml'), 31 => ($this->targetDirs[3].'/src/MBH/Bundle/ClientBundle/Resources/translations/messages.ru.yml'), 32 => ($this->targetDirs[3].'/src/MBH/Bundle/ClientBundle/Resources/translations/MBHClientBundle.ru.yml'), 33 => ($this->targetDirs[3].'/src/MBH/Bundle/VegaBundle/Resources/translations/messages.ru.yml'), 34 => ($this->targetDirs[3].'/src/MBH/Bundle/WarehouseBundle/Resources/translations/validators.ru.yml'), 35 => ($this->targetDirs[3].'/src/MBH/Bundle/WarehouseBundle/Resources/translations/messages.ru.yml'), 36 => ($this->targetDirs[3].'/src/MBH/Bundle/RestaurantBundle/Resources/translations/validators.ru.yml'), 37 => ($this->targetDirs[3].'/src/MBH/Bundle/RestaurantBundle/Resources/translations/messages.ru.yml')), 'sr_Cyrl' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sr_Cyrl.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.sr_Cyrl.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.sr_Cyrl.xlf')), 'lb' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.lb.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.lb.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.lb.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.lb.yml')), 'cy' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.cy.xlf')), 'hr' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.hr.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.hr.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.hr.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.hr.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.hr.yml')), 'et' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.et.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.et.xlf'), 2 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.et.yml')), 'pt_BR' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.pt_BR.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.pt_BR.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.pt_BR.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.pt_BR.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.pt_BR.yml')), 'mn' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.mn.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.mn.xlf')), 'af' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.af.xlf')), 'lt' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.lt.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.lt.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.lt.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.lt.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.lt.yml')), 'hy' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.hy.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.hy.xlf')), 'ja' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.ja.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.ja.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.ja.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.ja.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.ja.yml')), 'id' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.id.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.id.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.id.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.id.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.id.yml')), 'da' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.da.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.da.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.da.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.da.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.da.yml')), 'cs' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.cs.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.cs.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.cs.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.cs.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.cs.yml')), 'az' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.az.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.az.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.az.xlf')), 'uk' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.uk.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.uk.xlf'), 2 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.uk.yml'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.uk.yml')), 'no' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.no.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.no.xlf')), 'sl' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.sl.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.sl.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.sl.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.sl.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.sl.yml')), 'nl' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.nl.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.nl.xlf'), 2 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.nl.xlf'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.nl.yml'), 4 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.nl.yml')), 'nb' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Validator/Resources/translations/validators.nb.xlf'), 1 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.nb.xlf'), 2 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.nb.yml'), 3 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.nb.yml')), 'lv' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/translations/validators.lv.xlf'), 1 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/validators.lv.yml'), 2 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/translations/FOSUserBundle.lv.yml')), 'pt_PT' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.pt_PT.xlf')), 'ua' => array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Security/Core/Exception/../Resources/translations/security.ua.xlf')))), array());

        $instance->setConfigCacheFactory($this->get('config_cache_factory'));
        $instance->setFallbackLocales(array(0 => 'ru', 1 => 'ru', 2 => 'en'));

        return $instance;
    }

    /**
     * Gets the 'translator_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\TranslatorListener A Symfony\Component\HttpKernel\EventListener\TranslatorListener instance.
     */
    protected function getTranslatorListenerService()
    {
        return $this->services['translator_listener'] = new \Symfony\Component\HttpKernel\EventListener\TranslatorListener($this->get('translator'), $this->get('request_stack'));
    }

    /**
     * Gets the 'twig' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Twig_Environment A Twig_Environment instance.
     */
    protected function getTwigService()
    {
        $a = $this->get('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        $b = $this->get('request_stack');
        $c = $this->get('fragment.handler');

        $d = new \Symfony\Bridge\Twig\Extension\HttpFoundationExtension($b);

        $e = new \Symfony\Bridge\Twig\AppVariable();
        $e->setEnvironment('dev');
        $e->setDebug(true);
        if ($this->has('security.token_storage')) {
            $e->setTokenStorage($this->get('security.token_storage', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        }
        if ($this->has('request_stack')) {
            $e->setRequestStack($b);
        }
        $e->setContainer($this);

        $this->services['twig'] = $instance = new \Twig_Environment($this->get('twig.loader'), array('debug' => true, 'strict_variables' => true, 'base_template_class' => 'MBH\\Bundle\\BaseBundle\\Twig\\Template', 'exception_controller' => 'twig.controller.exception:showAction', 'form_themes' => array(0 => 'form_div_layout.html.twig'), 'autoescape' => 'filename', 'cache' => (__DIR__.'/twig'), 'charset' => 'UTF-8', 'paths' => array(), 'date' => array('format' => 'F j, Y H:i', 'interval_format' => '%d days', 'timezone' => NULL), 'number_format' => array('decimals' => 0, 'decimal_point' => '.', 'thousands_separator' => ',')));

        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\LogoutUrlExtension($this->get('security.logout_url_generator')));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\SecurityExtension($this->get('security.authorization_checker', ContainerInterface::NULL_ON_INVALID_REFERENCE)));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\ProfilerExtension($this->get('twig.profile'), $a));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension($this->get('translator')));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\AssetExtension($this->get('assets.packages'), $d));
        $instance->addExtension(new \Symfony\Bundle\TwigBundle\Extension\ActionsExtension($c));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\CodeExtension(NULL, $this->targetDirs[2], 'UTF-8'));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\RoutingExtension($this->get('router')));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\YamlExtension());
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\StopwatchExtension($a, true));
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\ExpressionExtension());
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\HttpKernelExtension($c));
        $instance->addExtension($d);
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\FormExtension(new \Symfony\Bridge\Twig\Form\TwigRenderer(new \Symfony\Bridge\Twig\Form\TwigRendererEngine(array(0 => 'form_div_layout.html.twig', 1 => 'LiipImagineBundle:Form:form_div_layout.html.twig')), $this->get('security.csrf.token_manager', ContainerInterface::NULL_ON_INVALID_REFERENCE))));
        $instance->addExtension(new \Twig_Extension_Debug());
        $instance->addExtension(new \Symfony\Bundle\AsseticBundle\Twig\AsseticExtension($this->get('assetic.asset_factory'), $this->get('templating.name_parser'), false, array(), array(0 => 'FrameworkBundle', 1 => 'SecurityBundle', 2 => 'TwigBundle', 3 => 'MonologBundle', 4 => 'SwiftmailerBundle', 5 => 'AsseticBundle', 6 => 'SensioFrameworkExtraBundle', 7 => 'DoctrineMongoDBBundle', 8 => 'StofDoctrineExtensionsBundle', 9 => 'FOSUserBundle', 10 => 'FOSJsRoutingBundle', 11 => 'KnpMenuBundle', 12 => 'ObHighchartsBundle', 13 => 'KnpSnappyBundle', 14 => 'MisdGuzzleBundle', 15 => 'IamPersistentMongoDBAclBundle', 16 => 'LiipImagineBundle', 17 => 'JMSDiExtraBundle', 18 => 'JMSAopBundle', 19 => 'JMSTranslationBundle', 20 => 'LiuggioExcelBundle', 21 => 'OrnicarGravatarBundle', 22 => 'DoctrineFixturesBundle', 23 => 'LswMemcacheBundle', 24 => 'MBHBaseBundle', 25 => 'MBHUserBundle', 26 => 'MBHHotelBundle', 27 => 'MBHPriceBundle', 28 => 'MBHPackageBundle', 29 => 'MBHCashBundle', 30 => 'MBHChannelManagerBundle', 31 => 'MBHOnlineBundle', 32 => 'MBHDemoBundle', 33 => 'MBHClientBundle', 34 => 'MBHVegaBundle', 35 => 'MBHWarehouseBundle', 36 => 'MBHRestaurantBundle', 37 => 'WebProfilerBundle', 38 => 'SensioDistributionBundle', 39 => 'SensioGeneratorBundle', 40 => 'DebugBundle'), new \Symfony\Bundle\AsseticBundle\DefaultValueSupplier($this)));
        $instance->addExtension(new \Knp\Menu\Twig\MenuExtension(new \Knp\Menu\Twig\Helper($this->get('knp_menu.renderer_provider'), $this->get('knp_menu.menu_provider'))));
        $instance->addExtension($this->get('ob_highcharts.twig.highcharts_extension'));
        $instance->addExtension(new \Liip\ImagineBundle\Templating\ImagineExtension($this->get('liip_imagine.cache.manager')));
        $instance->addExtension($this->get('jms_translation.twig_extension'));
        $instance->addExtension($this->get('twig.extension.gravatar'));
        $instance->addExtension($this->get('twig.text_extension'));
        $instance->addExtension($this->get('mbh.twig.hotel_selector_extension'));
        $instance->addExtension($this->get('mbh.twig.extension'));
        $instance->addExtension(new \Symfony\Bundle\WebProfilerBundle\Twig\WebProfilerExtension());
        $instance->addExtension(new \Symfony\Bridge\Twig\Extension\DumpExtension($this->get('var_dumper.cloner')));
        $instance->addGlobal('app', $e);
        $instance->addGlobal('meta_title', 'Система бронирования "MaxiBooking".');
        $instance->addGlobal('project_title', 'MaxiBooking');
        $instance->addGlobal('months', array(1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель', 5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'));
        $instance->addGlobal('weekdays', array(1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Вс'));
        $instance->addGlobal('environment', 'prod');
        $instance->addGlobal('version', '1.1.1');
        call_user_func(array(new \Symfony\Bundle\TwigBundle\DependencyInjection\Configurator\EnvironmentConfigurator('F j, Y H:i', '%d days', NULL, 0, '.', ','), 'configure'), $instance);

        return $instance;
    }

    /**
     * Gets the 'twig.controller.exception' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\TwigBundle\Controller\ExceptionController A Symfony\Bundle\TwigBundle\Controller\ExceptionController instance.
     */
    protected function getTwig_Controller_ExceptionService()
    {
        return $this->services['twig.controller.exception'] = new \Symfony\Bundle\TwigBundle\Controller\ExceptionController($this->get('twig'), true);
    }

    /**
     * Gets the 'twig.controller.preview_error' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\TwigBundle\Controller\PreviewErrorController A Symfony\Bundle\TwigBundle\Controller\PreviewErrorController instance.
     */
    protected function getTwig_Controller_PreviewErrorService()
    {
        return $this->services['twig.controller.preview_error'] = new \Symfony\Bundle\TwigBundle\Controller\PreviewErrorController($this->get('http_kernel'), 'twig.controller.exception:showAction');
    }

    /**
     * Gets the 'twig.exception_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ExceptionListener A Symfony\Component\HttpKernel\EventListener\ExceptionListener instance.
     */
    protected function getTwig_ExceptionListenerService()
    {
        return $this->services['twig.exception_listener'] = new \Symfony\Component\HttpKernel\EventListener\ExceptionListener('twig.controller.exception:showAction', $this->get('monolog.logger.request', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'twig.extension.gravatar' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Ornicar\GravatarBundle\Twig\GravatarExtension A Ornicar\GravatarBundle\Twig\GravatarExtension instance.
     */
    protected function getTwig_Extension_GravatarService()
    {
        return $this->services['twig.extension.gravatar'] = new \Ornicar\GravatarBundle\Twig\GravatarExtension($this->get('templating.helper.gravatar'));
    }

    /**
     * Gets the 'twig.loader' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader A Symfony\Bundle\TwigBundle\Loader\FilesystemLoader instance.
     */
    protected function getTwig_LoaderService()
    {
        $this->services['twig.loader'] = $instance = new \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader($this->get('templating.locator'), $this->get('templating.name_parser'));

        $instance->addPath(($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views'), 'Framework');
        $instance->addPath(($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bundle/SecurityBundle/Resources/views'), 'Security');
        $instance->addPath(($this->targetDirs[2].'/Resources/TwigBundle/views'), 'Twig');
        $instance->addPath(($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views'), 'Twig');
        $instance->addPath(($this->targetDirs[3].'/vendor/symfony/swiftmailer-bundle/Resources/views'), 'Swiftmailer');
        $instance->addPath(($this->targetDirs[3].'/vendor/doctrine/mongodb-odm-bundle/Resources/views'), 'DoctrineMongoDB');
        $instance->addPath(($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/views'), 'FOSUser');
        $instance->addPath(($this->targetDirs[3].'/vendor/misd/guzzle-bundle/Resources/views'), 'MisdGuzzle');
        $instance->addPath(($this->targetDirs[3].'/vendor/iampersistent/mongodb-acl-bundle/IamPersistent/MongoDBAclBundle/Resources/views'), 'IamPersistentMongoDBAcl');
        $instance->addPath(($this->targetDirs[3].'/vendor/liip/imagine-bundle/Liip/ImagineBundle/Resources/views'), 'LiipImagine');
        $instance->addPath(($this->targetDirs[3].'/vendor/jms/translation-bundle/JMS/TranslationBundle/Resources/views'), 'JMSTranslation');
        $instance->addPath(($this->targetDirs[3].'/vendor/leaseweb/memcache-bundle/Lsw/MemcacheBundle/Resources/views'), 'LswMemcache');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/BaseBundle/Resources/views'), 'MBHBase');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/UserBundle/Resources/views'), 'MBHUser');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/HotelBundle/Resources/views'), 'MBHHotel');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/PriceBundle/Resources/views'), 'MBHPrice');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/PackageBundle/Resources/views'), 'MBHPackage');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/CashBundle/Resources/views'), 'MBHCash');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/ChannelManagerBundle/Resources/views'), 'MBHChannelManager');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/OnlineBundle/Resources/views'), 'MBHOnline');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/ClientBundle/Resources/views'), 'MBHClient');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/VegaBundle/Resources/views'), 'MBHVega');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/WarehouseBundle/Resources/views'), 'MBHWarehouse');
        $instance->addPath(($this->targetDirs[3].'/src/MBH/Bundle/RestaurantBundle/Resources/views'), 'MBHRestaurant');
        $instance->addPath(($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views'), 'WebProfiler');
        $instance->addPath(($this->targetDirs[3].'/vendor/sensio/distribution-bundle/Sensio/Bundle/DistributionBundle/Resources/views'), 'SensioDistribution');
        $instance->addPath(($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bundle/DebugBundle/Resources/views'), 'Debug');
        $instance->addPath(($this->targetDirs[2].'/Resources/views'));
        $instance->addPath(($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Bridge/Twig/Resources/views/Form'));
        $instance->addPath(($this->targetDirs[3].'/vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views'));

        return $instance;
    }

    /**
     * Gets the 'twig.profile' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Twig_Profiler_Profile A Twig_Profiler_Profile instance.
     */
    protected function getTwig_ProfileService()
    {
        return $this->services['twig.profile'] = new \Twig_Profiler_Profile();
    }

    /**
     * Gets the 'twig.text_extension' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Twig_Extensions_Extension_Text A Twig_Extensions_Extension_Text instance.
     */
    protected function getTwig_TextExtensionService()
    {
        return $this->services['twig.text_extension'] = new \Twig_Extensions_Extension_Text();
    }

    /**
     * Gets the 'twig.translation.extractor' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bridge\Twig\Translation\TwigExtractor A Symfony\Bridge\Twig\Translation\TwigExtractor instance.
     */
    protected function getTwig_Translation_ExtractorService()
    {
        return $this->services['twig.translation.extractor'] = new \Symfony\Bridge\Twig\Translation\TwigExtractor($this->get('twig'));
    }

    /**
     * Gets the 'uri_signer' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\HttpKernel\UriSigner A Symfony\Component\HttpKernel\UriSigner instance.
     */
    protected function getUriSignerService()
    {
        return $this->services['uri_signer'] = new \Symfony\Component\HttpKernel\UriSigner('mySyperSecretKeyForSymfony');
    }

    /**
     * Gets the 'validator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface A Symfony\Component\Validator\Validator\ValidatorInterface instance.
     */
    protected function getValidatorService()
    {
        return $this->services['validator'] = $this->get('validator.builder')->getValidator();
    }

    /**
     * Gets the 'validator.builder' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Validator\ValidatorBuilderInterface A Symfony\Component\Validator\ValidatorBuilderInterface instance.
     */
    protected function getValidator_BuilderService()
    {
        $this->services['validator.builder'] = $instance = \Symfony\Component\Validator\Validation::createValidatorBuilder();

        $instance->setConstraintValidatorFactory(new \Symfony\Bundle\FrameworkBundle\Validator\ConstraintValidatorFactory($this, array('validator.expression' => 'validator.expression', 'Symfony\\Component\\Validator\\Constraints\\EmailValidator' => 'validator.email', 'security.validator.user_password' => 'security.validator.user_password', 'doctrine_odm.mongodb.unique' => 'doctrine_odm.mongodb.validator.unique', 'mbh_range' => 'mbh.validator.range', 'mbh.user.validator' => 'mbh.user.validator', 'mbh.package.validator' => 'mbh.package.validator')));
        $instance->setTranslator($this->get('translator'));
        $instance->setTranslationDomain('validators');
        $instance->addXmlMappings(array(0 => ($this->targetDirs[3].'/vendor/symfony/symfony/src/Symfony/Component/Form/Resources/config/validation.xml'), 1 => ($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/Resources/config/validation.xml')));
        $instance->addYamlMappings(array(0 => ($this->targetDirs[3].'/src/MBH/Bundle/UserBundle/Resources/config/validation.yml'), 1 => ($this->targetDirs[3].'/src/MBH/Bundle/PackageBundle/Resources/config/validation.yml')));
        $instance->enableAnnotationMapping($this->get('annotation_reader'));
        $instance->addMethodMapping('loadValidatorMetadata');
        $instance->addObjectInitializers(array(0 => $this->get('doctrine_odm.mongodb.validator_initializer'), 1 => new \FOS\UserBundle\Validator\Initializer($this->get('fos_user.user_manager'))));
        $instance->addXmlMapping(($this->targetDirs[3].'/vendor/friendsofsymfony/user-bundle/DependencyInjection/Compiler/../../Resources/config/storage-validation/mongodb.xml'));

        return $instance;
    }

    /**
     * Gets the 'validator.email' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Validator\Constraints\EmailValidator A Symfony\Component\Validator\Constraints\EmailValidator instance.
     */
    protected function getValidator_EmailService()
    {
        return $this->services['validator.email'] = new \Symfony\Component\Validator\Constraints\EmailValidator(false);
    }

    /**
     * Gets the 'validator.expression' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\Validator\Constraints\ExpressionValidator A Symfony\Component\Validator\Constraints\ExpressionValidator instance.
     */
    protected function getValidator_ExpressionService()
    {
        return $this->services['validator.expression'] = new \Symfony\Component\Validator\Constraints\ExpressionValidator($this->get('property_accessor'));
    }

    /**
     * Gets the 'var_dumper.cli_dumper' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\VarDumper\Dumper\CliDumper A Symfony\Component\VarDumper\Dumper\CliDumper instance.
     */
    protected function getVarDumper_CliDumperService()
    {
        return $this->services['var_dumper.cli_dumper'] = new \Symfony\Component\VarDumper\Dumper\CliDumper(NULL, 'UTF-8');
    }

    /**
     * Gets the 'var_dumper.cloner' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Component\VarDumper\Cloner\VarCloner A Symfony\Component\VarDumper\Cloner\VarCloner instance.
     */
    protected function getVarDumper_ClonerService()
    {
        $this->services['var_dumper.cloner'] = $instance = new \Symfony\Component\VarDumper\Cloner\VarCloner();

        $instance->setMaxItems(2500);
        $instance->setMaxString(-1);

        return $instance;
    }

    /**
     * Gets the 'web_profiler.controller.exception' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController A Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController instance.
     */
    protected function getWebProfiler_Controller_ExceptionService()
    {
        return $this->services['web_profiler.controller.exception'] = new \Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController($this->get('profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('twig'), true);
    }

    /**
     * Gets the 'web_profiler.controller.profiler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController A Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController instance.
     */
    protected function getWebProfiler_Controller_ProfilerService()
    {
        return $this->services['web_profiler.controller.profiler'] = new \Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController($this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('twig'), array('data_collector.request' => array(0 => 'request', 1 => '@WebProfiler/Collector/request.html.twig'), 'data_collector.time' => array(0 => 'time', 1 => '@WebProfiler/Collector/time.html.twig'), 'data_collector.memory' => array(0 => 'memory', 1 => '@WebProfiler/Collector/memory.html.twig'), 'data_collector.ajax' => array(0 => 'ajax', 1 => '@WebProfiler/Collector/ajax.html.twig'), 'data_collector.form' => array(0 => 'form', 1 => '@WebProfiler/Collector/form.html.twig'), 'data_collector.exception' => array(0 => 'exception', 1 => '@WebProfiler/Collector/exception.html.twig'), 'data_collector.logger' => array(0 => 'logger', 1 => '@WebProfiler/Collector/logger.html.twig'), 'data_collector.events' => array(0 => 'events', 1 => '@WebProfiler/Collector/events.html.twig'), 'data_collector.router' => array(0 => 'router', 1 => '@WebProfiler/Collector/router.html.twig'), 'data_collector.translation' => array(0 => 'translation', 1 => '@WebProfiler/Collector/translation.html.twig'), 'data_collector.security' => array(0 => 'security', 1 => '@Security/Collector/security.html.twig'), 'data_collector.twig' => array(0 => 'twig', 1 => '@WebProfiler/Collector/twig.html.twig'), 'data_collector.dump' => array(0 => 'dump', 1 => '@Debug/Profiler/dump.html.twig'), 'swiftmailer.data_collector' => array(0 => 'swiftmailer', 1 => '@Swiftmailer/Collector/swiftmailer.html.twig'), 'doctrine_mongodb.odm.data_collector.pretty' => array(0 => 'mongodb', 1 => 'DoctrineMongoDBBundle:Collector:mongodb'), 'misd_guzzle.data_collector' => array(0 => 'guzzle', 1 => 'MisdGuzzleBundle:Collector:guzzle'), 'memcache.data_collector' => array(0 => 'memcache', 1 => 'LswMemcacheBundle:Collector:memcache'), 'data_collector.config' => array(0 => 'config', 1 => '@WebProfiler/Collector/config.html.twig')), 'bottom');
    }

    /**
     * Gets the 'web_profiler.controller.router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\Controller\RouterController A Symfony\Bundle\WebProfilerBundle\Controller\RouterController instance.
     */
    protected function getWebProfiler_Controller_RouterService()
    {
        return $this->services['web_profiler.controller.router'] = new \Symfony\Bundle\WebProfilerBundle\Controller\RouterController($this->get('profiler', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('twig'), $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'web_profiler.debug_toolbar' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener A Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener instance.
     */
    protected function getWebProfiler_DebugToolbarService()
    {
        return $this->services['web_profiler.debug_toolbar'] = new \Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener($this->get('twig'), false, 2, 'bottom', $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE), '^/(app(_[\\w]+)?\\.php/)?_wdt');
    }

    /**
     * Gets the 'assetic.asset_factory' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Bundle\AsseticBundle\Factory\AssetFactory A Symfony\Bundle\AsseticBundle\Factory\AssetFactory instance.
     */
    protected function getAssetic_AssetFactoryService()
    {
        $this->services['assetic.asset_factory'] = $instance = new \Symfony\Bundle\AsseticBundle\Factory\AssetFactory($this->get('kernel'), $this, $this->getParameterBag(), ($this->targetDirs[2].'/../web'), true);

        $instance->addWorker(new \Assetic\Factory\Worker\EnsureFilterWorker('/\\.less$/', $this->get('assetic.filter.less')));

        return $instance;
    }

    /**
     * Gets the 'controller_name_converter' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser A Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser instance.
     */
    protected function getControllerNameConverterService()
    {
        return $this->services['controller_name_converter'] = new \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser($this->get('kernel'));
    }

    /**
     * Gets the 'doctrine_mongodb.odm.data_collector.pretty' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Doctrine\Bundle\MongoDBBundle\DataCollector\PrettyDataCollector A Doctrine\Bundle\MongoDBBundle\DataCollector\PrettyDataCollector instance.
     */
    protected function getDoctrineMongodb_Odm_DataCollector_PrettyService()
    {
        $this->services['doctrine_mongodb.odm.data_collector.pretty'] = $instance = new \Doctrine\Bundle\MongoDBBundle\DataCollector\PrettyDataCollector();

        $instance->setBatchInsertThreshold(4);

        return $instance;
    }

    /**
     * Gets the 'fos_user.document_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Doctrine\ODM\MongoDB\DocumentManager A Doctrine\ODM\MongoDB\DocumentManager instance.
     */
    protected function getFosUser_DocumentManagerService()
    {
        return $this->services['fos_user.document_manager'] = $this->get('doctrine_mongodb')->getManager(NULL);
    }

    /**
     * Gets the 'fos_user.user_provider.username_email' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \FOS\UserBundle\Security\EmailUserProvider A FOS\UserBundle\Security\EmailUserProvider instance.
     */
    protected function getFosUser_UserProvider_UsernameEmailService()
    {
        return $this->services['fos_user.user_provider.username_email'] = new \FOS\UserBundle\Security\EmailUserProvider($this->get('fos_user.user_manager'));
    }

    /**
     * Gets the 'jms_di_extra.controller_resolver' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \JMS\DiExtraBundle\HttpKernel\ControllerResolver A JMS\DiExtraBundle\HttpKernel\ControllerResolver instance.
     */
    protected function getJmsDiExtra_ControllerResolverService()
    {
        return $this->services['jms_di_extra.controller_resolver'] = new \JMS\DiExtraBundle\HttpKernel\ControllerResolver($this, $this->get('controller_name_converter'), $this->get('monolog.logger.request', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'misd_guzzle.listener.request_listener' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Misd\GuzzleBundle\EventListener\RequestListener A Misd\GuzzleBundle\EventListener\RequestListener instance.
     */
    protected function getMisdGuzzle_Listener_RequestListenerService()
    {
        return $this->services['misd_guzzle.listener.request_listener'] = new \Misd\GuzzleBundle\EventListener\RequestListener($this->get('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * Gets the 'misd_guzzle.log.adapter.array' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Guzzle\Log\ArrayLogAdapter A Guzzle\Log\ArrayLogAdapter instance.
     */
    protected function getMisdGuzzle_Log_Adapter_ArrayService()
    {
        return $this->services['misd_guzzle.log.adapter.array'] = new \Guzzle\Log\ArrayLogAdapter();
    }

    /**
     * Gets the 'misd_guzzle.log.array' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Guzzle\Plugin\Log\LogPlugin A Guzzle\Plugin\Log\LogPlugin instance.
     */
    protected function getMisdGuzzle_Log_ArrayService()
    {
        return $this->services['misd_guzzle.log.array'] = new \Guzzle\Plugin\Log\LogPlugin($this->get('misd_guzzle.log.adapter.array'));
    }

    /**
     * Gets the 'misd_guzzle.log.monolog' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Guzzle\Plugin\Log\LogPlugin A Guzzle\Plugin\Log\LogPlugin instance.
     */
    protected function getMisdGuzzle_Log_MonologService()
    {
        return $this->services['misd_guzzle.log.monolog'] = new \Guzzle\Plugin\Log\LogPlugin(new \Guzzle\Log\MonologLogAdapter($this->get('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE)), '{hostname} {req_header_User-Agent} - [{ts}] "{method} {resource} {protocol}/{version}" {code} {res_header_Content-Length}');
    }

    /**
     * Gets the 'router.request_context' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Component\Routing\RequestContext A Symfony\Component\Routing\RequestContext instance.
     */
    protected function getRouter_RequestContextService()
    {
        return $this->services['router.request_context'] = new \Symfony\Component\Routing\RequestContext(NULL, 'GET', 'live.maxibooking.ru', 'http', 80, 443);
    }

    /**
     * Gets the 'security.access.decision_manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Component\Security\Core\Authorization\AccessDecisionManager A Symfony\Component\Security\Core\Authorization\AccessDecisionManager instance.
     */
    protected function getSecurity_Access_DecisionManagerService()
    {
        $a = $this->get('security.role_hierarchy');
        $b = $this->get('security.authentication.trust_resolver');

        $this->services['security.access.decision_manager'] = $instance = new \Symfony\Component\Security\Core\Authorization\AccessDecisionManager(array(), 'affirmative', false, true);

        $instance->setVoters(array(0 => new \Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter($a), 1 => new \Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter(new \Symfony\Component\Security\Core\Authorization\ExpressionLanguage(), $b, $a), 2 => new \Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter($b), 3 => new \Symfony\Component\Security\Acl\Voter\AclVoter($this->get('security.acl.provider'), new \Symfony\Component\Security\Acl\Domain\ObjectIdentityRetrievalStrategy(), new \Symfony\Component\Security\Acl\Domain\SecurityIdentityRetrievalStrategy($a, $b), new \Symfony\Component\Security\Acl\Permission\BasicPermissionMap(), $this->get('monolog.logger.security', ContainerInterface::NULL_ON_INVALID_REFERENCE), true)));

        return $instance;
    }

    /**
     * Gets the 'security.authentication.manager' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager A Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager instance.
     */
    protected function getSecurity_Authentication_ManagerService()
    {
        $a = $this->get('security.user_checker.main');

        $this->services['security.authentication.manager'] = $instance = new \Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager(array(0 => new \Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider($this->get('fos_user.user_provider.username_email'), $a, 'main', $this->get('security.encoder_factory'), true), 1 => new \Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider($a, 'mySyperSecretKeyForSymfony', 'main'), 2 => new \Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider('578dd7d7a6f396.63738139')), true);

        $instance->setEventDispatcher($this->get('debug.event_dispatcher'));

        return $instance;
    }

    /**
     * Gets the 'security.authentication.session_strategy' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy A Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy instance.
     */
    protected function getSecurity_Authentication_SessionStrategyService()
    {
        return $this->services['security.authentication.session_strategy'] = new \Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy('migrate');
    }

    /**
     * Gets the 'security.authentication.trust_resolver' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver A Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver instance.
     */
    protected function getSecurity_Authentication_TrustResolverService()
    {
        return $this->services['security.authentication.trust_resolver'] = new \Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver('Symfony\\Component\\Security\\Core\\Authentication\\Token\\AnonymousToken', 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken');
    }

    /**
     * Gets the 'security.http_utils' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Component\Security\Http\HttpUtils A Symfony\Component\Security\Http\HttpUtils instance.
     */
    protected function getSecurity_HttpUtilsService()
    {
        $a = $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE);

        return $this->services['security.http_utils'] = new \Symfony\Component\Security\Http\HttpUtils($a, $a);
    }

    /**
     * Gets the 'security.logout_url_generator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Component\Security\Http\Logout\LogoutUrlGenerator A Symfony\Component\Security\Http\Logout\LogoutUrlGenerator instance.
     */
    protected function getSecurity_LogoutUrlGeneratorService()
    {
        $this->services['security.logout_url_generator'] = $instance = new \Symfony\Component\Security\Http\Logout\LogoutUrlGenerator($this->get('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('router', ContainerInterface::NULL_ON_INVALID_REFERENCE), $this->get('security.token_storage', ContainerInterface::NULL_ON_INVALID_REFERENCE));

        $instance->registerListener('main', '/user/logout', 'logout', '_csrf_token', NULL);

        return $instance;
    }

    /**
     * Gets the 'security.role_hierarchy' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Component\Security\Core\Role\RoleHierarchy A Symfony\Component\Security\Core\Role\RoleHierarchy instance.
     */
    protected function getSecurity_RoleHierarchyService()
    {
        return $this->services['security.role_hierarchy'] = new \Symfony\Component\Security\Core\Role\RoleHierarchy(array('ROLE_SUPER_ADMIN' => array(0 => 'ROLE_ADMIN'), 'ROLE_ADMIN' => array(0 => 'ROLE_HOTEL', 1 => 'ROLE_GROUP', 2 => 'ROLE_CITY', 3 => 'ROLE_LOGS', 4 => 'ROLE_CASH', 5 => 'ROLE_CLIENT_CONFIG', 6 => 'ROLE_DOCUMENT_TEMPLATES', 7 => 'ROLE_HOUSING', 8 => 'ROLE_ROOM', 9 => 'ROLE_ROOM_TYPE', 10 => 'ROLE_TASK_MANAGER', 11 => 'ROLE_MANAGER', 12 => 'ROLE_OVERVIEW', 13 => 'ROLE_PRICE_CACHE', 14 => 'ROLE_RESTRICTION', 15 => 'ROLE_ROOM_CACHE', 16 => 'ROLE_SERVICE', 17 => 'ROLE_SERVICE_CATEGORY', 18 => 'ROLE_TARIFF', 19 => 'ROLE_USER', 20 => 'ROLE_CHANNEL_MANAGER', 21 => 'ROLE_ONLINE_FORM', 22 => 'ROLE_POLLS', 23 => 'ROLE_REPORTS', 24 => 'ROLE_PACKAGE', 25 => 'ROLE_SOURCE', 26 => 'ROLE_PROMOTION', 27 => 'ROLE_ROOM_TYPE_CATEGORY', 28 => 'ROLE_WORK_SHIFT', 29 => 'ROLE_WAREHOUSE', 30 => 'ROLE_RESTAURANT'), 'ROLE_LOGS' => array(0 => 'ROLE_BASE_USER'), 'ROLE_USER' => array(0 => 'ROLE_USER_VIEW', 1 => 'ROLE_USER_NEW', 2 => 'ROLE_USER_EDIT', 3 => 'ROLE_USER_DELETE', 4 => 'ROLE_USER_PROFILE'), 'ROLE_GROUP' => array(0 => 'ROLE_GROUP_VIEW', 1 => 'ROLE_GROUP_NEW', 2 => 'ROLE_GROUP_EDIT', 3 => 'ROLE_GROUP_DELETE'), 'ROLE_HOTEL' => array(0 => 'ROLE_HOTEL_VIEW', 1 => 'ROLE_HOTEL_NEW', 2 => 'ROLE_HOTEL_EDIT', 3 => 'ROLE_HOTEL_DELETE'), 'ROLE_CITY' => array(0 => 'ROLE_CITY_VIEW'), 'ROLE_CASH' => array(0 => 'ROLE_CASH_VIEW', 1 => 'ROLE_CASH_NEW', 2 => 'ROLE_CASH_EDIT', 3 => 'ROLE_CASH_DELETE', 4 => 'ROLE_CASH_CONFIRM', 5 => 'ROLE_CASH_NUMBER'), 'ROLE_CLIENT_CONFIG' => array(0 => 'ROLE_CLIENT_CONFIG_VIEW', 1 => 'ROLE_CLIENT_CONFIG_EDIT'), 'ROLE_DOCUMENT_TEMPLATES' => array(0 => 'ROLE_DOCUMENT_TEMPLATES_VIEW', 1 => 'ROLE_DOCUMENT_TEMPLATES_NEW', 2 => 'ROLE_DOCUMENT_TEMPLATES_EDIT', 3 => 'ROLE_DOCUMENT_TEMPLATES_DELETE'), 'ROLE_ROOM_FUND' => array(0 => 'ROLE_ROOM_TYPE', 1 => 'ROLE_ROOM'), 'ROLE_HOUSING' => array(0 => 'ROLE_HOUSING_VIEW', 1 => 'ROLE_HOUSING_NEW', 2 => 'ROLE_HOUSING_EDIT', 3 => 'ROLE_HOUSING_DELETE'), 'ROLE_ROOM_TYPE' => array(0 => 'ROLE_ROOM_TYPE_VIEW', 1 => 'ROLE_ROOM_TYPE_NEW', 2 => 'ROLE_ROOM_TYPE_EDIT', 3 => 'ROLE_ROOM_TYPE_DELETE'), 'ROLE_ROOM' => array(0 => 'ROLE_ROOM_VIEW', 1 => 'ROLE_ROOM_NEW', 2 => 'ROLE_ROOM_EDIT', 3 => 'ROLE_ROOM_DELETE', 4 => 'ROLE_ROOM_STATUS_EDIT'), 'ROLE_CHANNEL_MANAGER' => array(0 => 'ROLE_BOOKING', 1 => 'ROLE_VASHOTEL', 2 => 'ROLE_OSTROVOK', 3 => 'ROLE_OKTOGO', 4 => 'ROLE_HOTELINN', 5 => 'ROLE_MYALLOCATOR'), 'ROLE_ONLINE_FORM' => array(0 => 'ROLE_ONLINE_FORM_VIEW', 1 => 'ROLE_ONLINE_FORM_NEW', 2 => 'ROLE_ONLINE_FORM_EDIT', 3 => 'ROLE_ONLINE_FORM_DELETE'), 'ROLE_POLLS' => array(0 => 'ROLE_BASE_USER'), 'ROLE_PACKAGE' => array(0 => 'ROLE_SEARCH', 1 => 'ROLE_PACKAGE_VIEW', 2 => 'ROLE_PACKAGE_NEW', 3 => 'ROLE_ORDER_EDIT', 4 => 'ROLE_PACKAGE_EDIT', 5 => 'ROLE_ORDER_PAYER', 6 => 'ROLE_PACKAGE_GUESTS', 7 => 'ROLE_PACKAGE_SERVICES', 8 => 'ROLE_PACKAGE_ACCOMMODATION', 9 => 'ROLE_ORDER_DOCUMENTS', 10 => 'ROLE_DOCUMENTS_GENERATOR', 11 => 'ROLE_ORDER_CASH_DOCUMENTS', 12 => 'ROLE_PACKAGE_DELETE', 13 => 'ROLE_PACKAGE_VIEW_ALL', 14 => 'ROLE_PACKAGE_EDIT_ALL', 15 => 'ROLE_PACKAGE_DELETE_ALL', 16 => 'ROLE_PACKAGE_DOCS', 17 => 'ROLE_ORDER_AUTO_CONFIRMATION', 18 => 'ROLE_INDIVIDUAL_PROMOTION_ADD', 19 => 'ROLE_PROMOTION_ADD', 20 => 'ROLE_DISCOUNT_ADD', 21 => 'ROLE_PACKAGE_PRICE_EDIT', 22 => 'ROLE_FORCE_BOOKING'), 'ROLE_PACKAGE_VIEW_ALL' => array(0 => 'ROLE_PACKAGE_VIEW'), 'ROLE_PACKAGE_DELETE_ALL' => array(0 => 'ROLE_PACKAGE_DELETE'), 'ROLE_REPORTS' => array(0 => 'ROLE_ANALYTICS', 1 => 'ROLE_TOURIST_REPORT', 2 => 'ROLE_PORTER_REPORT', 3 => 'ROLE_ACCOMMODATION_REPORT', 4 => 'ROLE_SERVICES_REPORT', 5 => 'ROLE_ORGANIZATION', 6 => 'ROLE_MANAGERS_REPORT', 7 => 'ROLE_POLLS_REPORT', 8 => 'ROLE_ROOMS_REPORT'), 'ROLE_TOURIST_REPORT' => array(0 => 'ROLE_TOURIST'), 'ROLE_TOURIST' => array(0 => 'ROLE_TOURIST_VIEW', 1 => 'ROLE_TOURIST_NEW', 2 => 'ROLE_TOURIST_EDIT', 3 => 'ROLE_TOURIST_DELETE'), 'ROLE_ORGANIZATION' => array(0 => 'ROLE_ORGANIZATION_VIEW', 1 => 'ROLE_ORGANIZATION_NEW', 2 => 'ROLE_ORGANIZATION_EDIT', 3 => 'ROLE_ORGANIZATION_DELETE'), 'ROLE_SOURCE' => array(0 => 'ROLE_SOURCE_VIEW', 1 => 'ROLE_SOURCE_NEW', 2 => 'ROLE_SOURCE_EDIT', 3 => 'ROLE_SOURCE_DELETE'), 'ROLE_TASK_MANAGER' => array(0 => 'ROLE_TASK', 1 => 'ROLE_TASK_TYPE', 2 => 'ROLE_TASK_TYPE_CATEGORY'), 'ROLE_TASK' => array(0 => 'ROLE_TASK_VIEW', 1 => 'ROLE_TASK_OWN_VIEW', 2 => 'ROLE_TASK_NEW', 3 => 'ROLE_TASK_EDIT', 4 => 'ROLE_TASK_DELETE'), 'ROLE_TASK_TYPE' => array(0 => 'ROLE_TASK_TYPE_VIEW', 1 => 'ROLE_TASK_TYPE_NEW', 2 => 'ROLE_TASK_TYPE_EDIT', 3 => 'ROLE_TASK_TYPE_DELETE'), 'ROLE_TASK_TYPE_CATEGORY' => array(0 => 'ROLE_TASK_TYPE_CATEGORY_VIEW', 1 => 'ROLE_TASK_TYPE_CATEGORY_NEW', 2 => 'ROLE_TASK_TYPE_CATEGORY_EDIT', 3 => 'ROLE_TASK_TYPE_CATEGORY_DELETE'), 'ROLE_PRICE_CACHE' => array(0 => 'ROLE_PRICE_CACHE_VIEW', 1 => 'ROLE_PRICE_CACHE_EDIT', 2 => 'ROLE_OVERVIEW'), 'ROLE_RESTRICTION' => array(0 => 'ROLE_RESTRICTION_VIEW', 1 => 'ROLE_RESTRICTION_EDIT', 2 => 'ROLE_OVERVIEW'), 'ROLE_ROOM_CACHE' => array(0 => 'ROLE_ROOM_CACHE_VIEW', 1 => 'ROLE_ROOM_CACHE_EDIT', 2 => 'ROLE_OVERVIEW'), 'ROLE_SERVICE' => array(0 => 'ROLE_SERVICE_VIEW', 1 => 'ROLE_SERVICE_NEW', 2 => 'ROLE_SERVICE_EDIT', 3 => 'ROLE_SERVICE_DELETE'), 'ROLE_SERVICE_CATEGORY' => array(0 => 'ROLE_SERVICE_CATEGORY_NEW', 1 => 'ROLE_SERVICE_CATEGORY_EDIT', 2 => 'ROLE_SERVICE_CATEGORY_DELETE'), 'ROLE_TARIFF' => array(0 => 'ROLE_TARIFF_VIEW', 1 => 'ROLE_TARIFF_NEW', 2 => 'ROLE_TARIFF_EDIT', 3 => 'ROLE_TARIFF_DELETE'), 'ROLE_PROMOTION' => array(0 => 'ROLE_PROMOTION_VIEW', 1 => 'ROLE_PROMOTION_NEW', 2 => 'ROLE_PROMOTION_EDIT', 3 => 'ROLE_PROMOTION_DELETE'), 'ROLE_ROOM_TYPE_CATEGORY' => array(0 => 'ROLE_ROOM_TYPE_CATEGORY_VIEW', 1 => 'ROLE_ROOM_TYPE_CATEGORY_NEW', 2 => 'ROLE_ROOM_TYPE_CATEGORY_EDIT', 3 => 'ROLE_ROOM_TYPE_CATEGORY_DELETE'), 'ROLE_WORK_SHIFT' => array(0 => 'ROLE_WORK_SHIFT_VIEW', 1 => 'ROLE_WORK_SHIFT_CLOSE'), 'ROLE_STAFF' => array(0 => 'ROLE_TASK_OWN_VIEW'), 'ROLE_WAREHOUSE' => array(0 => 'ROLE_WAREHOUSE_CAT', 1 => 'ROLE_WAREHOUSE_ITEMS', 2 => 'ROLE_WAREHOUSE_RECORD', 3 => 'ROLE_WAREHOUSE_INVOICE'), 'ROLE_WAREHOUSE_CAT' => array(0 => 'ROLE_WAREHOUSE_CAT_VIEW', 1 => 'ROLE_WAREHOUSE_CAT_NEW', 2 => 'ROLE_WAREHOUSE_CAT_EDIT', 3 => 'ROLE_WAREHOUSE_CAT_DELETE'), 'ROLE_WAREHOUSE_ITEMS' => array(0 => 'ROLE_WAREHOUSE_ITEMS_VIEW', 1 => 'ROLE_WAREHOUSE_ITEMS_NEW', 2 => 'ROLE_WAREHOUSE_ITEMS_EDIT', 3 => 'ROLE_WAREHOUSE_ITEMS_DELETE'), 'ROLE_WAREHOUSE_RECORD' => array(0 => 'ROLE_WAREHOUSE_RECORD_VIEW', 1 => 'ROLE_WAREHOUSE_RECORD_NEW', 2 => 'ROLE_WAREHOUSE_RECORD_EDIT', 3 => 'ROLE_WAREHOUSE_RECORD_DELETE'), 'ROLE_WAREHOUSE_INVOICE' => array(0 => 'ROLE_WAREHOUSE_INVOICE_VIEW', 1 => 'ROLE_WAREHOUSE_INVOICE_NEW', 2 => 'ROLE_WAREHOUSE_INVOICE_EDIT', 3 => 'ROLE_WAREHOUSE_INVOICE_DELETE'), 'ROLE_RESTAURANT' => array(0 => 'ROLE_RESTAURANT_CATEGORY', 1 => 'ROLE_RESTAURANT_INGREDIENT', 2 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY', 3 => 'ROLE_RESTAURANT_DISHMENU_ITEM', 4 => 'ROLE_RESTAURANT_ORDER_MANAGER', 5 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED', 6 => 'ROLE_RESTAURANT_TABLE'), 'ROLE_RESTAURANT_CATEGORY' => array(0 => 'ROLE_RESTAURANT_CATEGORY_NEW', 1 => 'ROLE_RESTAURANT_CATEGORY_EDIT', 2 => 'ROLE_RESTAURANT_CATEGORY_DELETE'), 'ROLE_RESTAURANT_INGREDIENT' => array(0 => 'ROLE_RESTAURANT_INGREDIENT_VIEW', 1 => 'ROLE_RESTAURANT_INGREDIENT_NEW', 2 => 'ROLE_RESTAURANT_INGREDIENT_EDIT', 3 => 'ROLE_RESTAURANT_INGREDIENT_DELETE'), 'ROLE_RESTAURANT_DISHMENU_CATEGORY' => array(0 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_NEW', 1 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_EDIT', 2 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_DELETE'), 'ROLE_RESTAURANT_DISHMENU_ITEM' => array(0 => 'ROLE_RESTAURANT_DISHMENU_ITEM_VIEW', 1 => 'ROLE_RESTAURANT_DISHMENU_ITEM_NEW', 2 => 'ROLE_RESTAURANT_DISHMENU_ITEM_EDIT', 3 => 'ROLE_RESTAURANT_DISHMENU_ITEM_DELETE'), 'ROLE_RESTAURANT_ORDER_MANAGER' => array(0 => 'ROLE_RESTAURANT_ORDER_MANAGER_VIEW', 1 => 'ROLE_RESTAURANT_ORDER_MANAGER_NEW', 2 => 'ROLE_RESTAURANT_ORDER_MANAGER_EDIT', 3 => 'ROLE_RESTAURANT_ORDER_MANAGER_DELETE', 4 => 'ROLE_RESTAURANT_ORDER_MANAGER_PAY'), 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED' => array(0 => 'ROLE_RESTAURANT_ORDER_MANAGER', 1 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_EDIT', 2 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_DELETE'), 'ROLE_RESTAURANT_TABLE' => array(0 => 'ROLE_RESTAURANT_TABLE_VIEW', 1 => 'ROLE_RESTAURANT_TABLE_NEW', 2 => 'ROLE_RESTAURANT_TABLE_EDIT', 3 => 'ROLE_RESTAURANT_TABLE_DELETE')));
    }

    /**
     * Gets the 'session.storage.metadata_bag' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag A Symfony\Component\HttpFoundation\Session\Storage\MetadataBag instance.
     */
    protected function getSession_Storage_MetadataBagService()
    {
        return $this->services['session.storage.metadata_bag'] = new \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag('_sf2_meta', '0');
    }

    /**
     * Gets the 'stof_doctrine_extensions.listener.blameable' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Gedmo\Blameable\BlameableListener A Gedmo\Blameable\BlameableListener instance.
     */
    protected function getStofDoctrineExtensions_Listener_BlameableService()
    {
        $this->services['stof_doctrine_extensions.listener.blameable'] = $instance = new \Gedmo\Blameable\BlameableListener();

        $instance->setAnnotationReader($this->get('annotation_reader'));

        return $instance;
    }

    /**
     * Gets the 'stof_doctrine_extensions.listener.loggable' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Gedmo\Loggable\LoggableListener A Gedmo\Loggable\LoggableListener instance.
     */
    protected function getStofDoctrineExtensions_Listener_LoggableService()
    {
        $this->services['stof_doctrine_extensions.listener.loggable'] = $instance = new \Gedmo\Loggable\LoggableListener();

        $instance->setAnnotationReader($this->get('annotation_reader'));

        return $instance;
    }

    /**
     * Gets the 'stof_doctrine_extensions.listener.translatable' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Gedmo\Translatable\TranslatableListener A Gedmo\Translatable\TranslatableListener instance.
     */
    protected function getStofDoctrineExtensions_Listener_TranslatableService()
    {
        $this->services['stof_doctrine_extensions.listener.translatable'] = $instance = new \Gedmo\Translatable\TranslatableListener();

        $instance->setAnnotationReader($this->get('annotation_reader'));
        $instance->setDefaultLocale('ru_RU');
        $instance->setTranslatableLocale('ru_RU');
        $instance->setTranslationFallback(true);
        $instance->setPersistDefaultLocaleTranslation(false);
        $instance->setSkipOnLoad(false);

        return $instance;
    }

    /**
     * Gets the 'swiftmailer.mailer.default.transport.eventdispatcher' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Swift_Events_SimpleEventDispatcher A Swift_Events_SimpleEventDispatcher instance.
     */
    protected function getSwiftmailer_Mailer_Default_Transport_EventdispatcherService()
    {
        return $this->services['swiftmailer.mailer.default.transport.eventdispatcher'] = new \Swift_Events_SimpleEventDispatcher();
    }

    /**
     * Gets the 'templating.locator' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * This service is private.
     * If you want to be able to request this service from the container directly,
     * make it public, otherwise you might end up with broken code.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator A Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator instance.
     */
    protected function getTemplating_LocatorService()
    {
        return $this->services['templating.locator'] = new \Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator($this->get('file_locator'), __DIR__);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        $name = strtolower($name);

        if (!(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters))) {
            throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter($name)
    {
        $name = strtolower($name);

        return isset($this->parameters[$name]) || array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $this->parameterBag = new FrozenParameterBag($this->parameters);
        }

        return $this->parameterBag;
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'kernel.root_dir' => $this->targetDirs[2],
            'kernel.environment' => 'dev',
            'kernel.debug' => true,
            'kernel.name' => 'app',
            'kernel.cache_dir' => __DIR__,
            'kernel.logs_dir' => ($this->targetDirs[2].'/logs'),
            'kernel.bundles' => array(
                'FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
                'SecurityBundle' => 'Symfony\\Bundle\\SecurityBundle\\SecurityBundle',
                'TwigBundle' => 'Symfony\\Bundle\\TwigBundle\\TwigBundle',
                'MonologBundle' => 'Symfony\\Bundle\\MonologBundle\\MonologBundle',
                'SwiftmailerBundle' => 'Symfony\\Bundle\\SwiftmailerBundle\\SwiftmailerBundle',
                'AsseticBundle' => 'Symfony\\Bundle\\AsseticBundle\\AsseticBundle',
                'SensioFrameworkExtraBundle' => 'Sensio\\Bundle\\FrameworkExtraBundle\\SensioFrameworkExtraBundle',
                'DoctrineMongoDBBundle' => 'Doctrine\\Bundle\\MongoDBBundle\\DoctrineMongoDBBundle',
                'StofDoctrineExtensionsBundle' => 'Stof\\DoctrineExtensionsBundle\\StofDoctrineExtensionsBundle',
                'FOSUserBundle' => 'FOS\\UserBundle\\FOSUserBundle',
                'FOSJsRoutingBundle' => 'FOS\\JsRoutingBundle\\FOSJsRoutingBundle',
                'KnpMenuBundle' => 'Knp\\Bundle\\MenuBundle\\KnpMenuBundle',
                'ObHighchartsBundle' => 'Ob\\HighchartsBundle\\ObHighchartsBundle',
                'KnpSnappyBundle' => 'Knp\\Bundle\\SnappyBundle\\KnpSnappyBundle',
                'MisdGuzzleBundle' => 'Misd\\GuzzleBundle\\MisdGuzzleBundle',
                'IamPersistentMongoDBAclBundle' => 'IamPersistent\\MongoDBAclBundle\\IamPersistentMongoDBAclBundle',
                'LiipImagineBundle' => 'Liip\\ImagineBundle\\LiipImagineBundle',
                'JMSDiExtraBundle' => 'JMS\\DiExtraBundle\\JMSDiExtraBundle',
                'JMSAopBundle' => 'JMS\\AopBundle\\JMSAopBundle',
                'JMSTranslationBundle' => 'JMS\\TranslationBundle\\JMSTranslationBundle',
                'LiuggioExcelBundle' => 'Liuggio\\ExcelBundle\\LiuggioExcelBundle',
                'OrnicarGravatarBundle' => 'Ornicar\\GravatarBundle\\OrnicarGravatarBundle',
                'DoctrineFixturesBundle' => 'Doctrine\\Bundle\\FixturesBundle\\DoctrineFixturesBundle',
                'LswMemcacheBundle' => 'Lsw\\MemcacheBundle\\LswMemcacheBundle',
                'MBHBaseBundle' => 'MBH\\Bundle\\BaseBundle\\MBHBaseBundle',
                'MBHUserBundle' => 'MBH\\Bundle\\UserBundle\\MBHUserBundle',
                'MBHHotelBundle' => 'MBH\\Bundle\\HotelBundle\\MBHHotelBundle',
                'MBHPriceBundle' => 'MBH\\Bundle\\PriceBundle\\MBHPriceBundle',
                'MBHPackageBundle' => 'MBH\\Bundle\\PackageBundle\\MBHPackageBundle',
                'MBHCashBundle' => 'MBH\\Bundle\\CashBundle\\MBHCashBundle',
                'MBHChannelManagerBundle' => 'MBH\\Bundle\\ChannelManagerBundle\\MBHChannelManagerBundle',
                'MBHOnlineBundle' => 'MBH\\Bundle\\OnlineBundle\\MBHOnlineBundle',
                'MBHDemoBundle' => 'MBH\\Bundle\\DemoBundle\\MBHDemoBundle',
                'MBHClientBundle' => 'MBH\\Bundle\\ClientBundle\\MBHClientBundle',
                'MBHVegaBundle' => 'MBH\\Bundle\\VegaBundle\\MBHVegaBundle',
                'MBHWarehouseBundle' => 'MBH\\Bundle\\WarehouseBundle\\MBHWarehouseBundle',
                'MBHRestaurantBundle' => 'MBH\\Bundle\\RestaurantBundle\\MBHRestaurantBundle',
                'WebProfilerBundle' => 'Symfony\\Bundle\\WebProfilerBundle\\WebProfilerBundle',
                'SensioDistributionBundle' => 'Sensio\\Bundle\\DistributionBundle\\SensioDistributionBundle',
                'SensioGeneratorBundle' => 'Sensio\\Bundle\\GeneratorBundle\\SensioGeneratorBundle',
                'DebugBundle' => 'Symfony\\Bundle\\DebugBundle\\DebugBundle',
            ),
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => 'appDevDebugProjectContainer',
            'mailer_transport' => 'smtp',
            'mailer_host' => 'smtp.yandex.ru',
            'mailer_user' => 'robot@maxi-booking.ru',
            'mailer_password' => 'ghjlflbv10rjgbq',
            'mongodb_url' => 'mongodb://localhost:27017/mbh',
            'mongodb_database' => 'mbh',
            'secret' => 'mySyperSecretKeyForSymfony',
            'mbhs_key' => 'AMliD0HLwjtLNewcrcNLr2xm8SGNY9HzV2Rz3zlA',
            'mbh_environment' => 'prod',
            'locale' => 'ru',
            'locale.currency' => 'rub',
            'debug_toolbar' => true,
            'debug_redirects' => false,
            'use_assetic_controller' => false,
            'router.request_context.host' => 'live.maxibooking.ru',
            'router.request_context.scheme' => 'http',
            'router.request_context.base_url' => NULL,
            'mbh_timezone' => 'default',
            'mbh_package_arrival_time' => 12,
            'mbh_package_departure_time' => 14,
            'mbh_package_notpaid_time' => '-5 days',
            'mailer_user_arrival_links' => array(
                'about' => 'http://example.com/',
                'rooms' => 'http://example.com/',
                'map' => 'http://example.com/',
                'contacts' => 'http://example.com/',
                'poll' => 'http://example.com',
            ),
            'mbh_payment_systems_change' => true,
            'currency_ratio_fix' => 1.0149999999999999,
            'online_form_sites' => array(
                0 => 'http://test.h',
                1 => 'http://example.com',
            ),
            'online_form_result_url' => 'http://test.h/hotel/results.html',
            'mbh_cache' => array(
                'is_enabled' => false,
                'prefix' => 'mbh',
            ),
            'mbh_modules' => array(
                'tasks' => true,
                'online_export' => true,
            ),
            'mbh.version' => '1.1.1',
            'mbh.currency.ratio.fix' => 1.0149999999999999,
            'mbh.currency.data' => array(
                'rub' => array(
                    'text' => 'руб.',
                    'small' => 'коп.',
                    'icon' => 'fa fa-ruble',
                ),
                'eur' => array(
                    'text' => '€',
                    'small' => '¢',
                    'icon' => 'fa fa-eur',
                ),
                'uah' => array(
                    'text' => 'грн.',
                    'small' => 'коп.',
                    'icon' => 'fa fa-money',
                ),
                'kzt' => array(
                    'text' => 'тенге',
                    'small' => 'тиын',
                    'icon' => 'fa fa-money',
                ),
                'gel' => array(
                    'text' => 'Lari',
                    'small' => 't',
                    'icon' => 'fa fa-money',
                ),
            ),
            'mbh.timezone' => 'default',
            'mbh.weekdays' => array(
                1 => 'Понедельник',
                2 => 'Вторник',
                3 => 'Среда',
                4 => 'Четверг',
                5 => 'Пятница',
                6 => 'Суббота',
                0 => 'Воскресенье',
            ),
            'mbh.mongodb' => array(
                'url' => 'mongodb://localhost:27017/mbh',
                'db' => 'mbh',
            ),
            'mbh.environment' => 'prod',
            'mbh.hotel' => array(
                'types' => array(
                    'apartments' => 'Апартаменты',
                    'recreation_center' => 'База отдыха',
                    'guest_house' => 'Гостевой дом',
                    'pension' => 'Пансионат',
                    'sanatorium' => 'Санаторий',
                    'country_club' => 'Загородный клуб',
                    'hostel' => 'Хостел',
                    'hotel' => 'Отель',
                    'motel' => 'Мотель',
                    'resort' => 'Курортный отель',
                    'mini_hotel' => 'Мини-отель',
                ),
                'themes' => array(
                    'сity_tour' => 'Экскурсия по городу',
                    'budget_travel' => 'Бюджетные и молодежные поездки',
                    'shoping' => 'Шопинг',
                    'family_trip' => 'Семейная поездка',
                    'international' => 'Международный',
                    'business_trip' => 'Деловая поездка',
                    'romantic_vacation' => 'Романтический отпуск/Медовый месяц',
                    'interior_design' => 'Дизайнерский интерьер',
                    'luxury_holidays' => 'Отдых класса люкс',
                    'haute_cuisine' => 'Изысканная кухня',
                    'country_holidays' => 'Загородный отдых',
                    'homestead' => 'Усадьба',
                    'nature' => 'Природа/Дикая флора и фауна',
                    'spa' => 'Спа/Оздоровительные процедуры',
                    'ecological' => 'Экологический',
                    'ski' => 'Горы/лыжи',
                    'adventure' => 'Приключение',
                    'sport' => 'Спорт',
                    'beach' => 'Пляж/Побережье',
                    'city' => 'Городской',
                ),
                'facilities' => array(
                    'building' => array(
                        'parking' => 'Парковка',
                        'fitness' => 'Фитнес-центр',
                        'swimming' => 'Бассейн',
                        'sauna' => 'Баня/Сауна',
                        'internet' => 'Интернет-класс',
                        'shopping' => 'Шопинг',
                        'restaurant' => 'Ресторан',
                        'cafeteria' => 'Кафетерий',
                        'fastfood' => 'Закусочная',
                        'beach-bar' => 'Бар на пляже',
                        'beer' => 'Пивной бар',
                        'baggage-safe' => 'Комната хранения',
                        'baggage-man' => 'Носильщик',
                        'disc-floppy' => 'Гостевой офис',
                        'spa' => 'Спа-салон',
                        'lounge' => 'Зона отдыха',
                        'skiing' => 'Лыжная трасса',
                        'chapel' => 'Часовня',
                    ),
                    'rules' => array(
                        'animals' => 'Разрешены животные',
                        'no-animals' => 'Размещение с животными запрещено',
                        'credit-card' => 'Кредитные карты принимаются',
                        'cc-nc' => 'Иностранная валюта не принимается',
                        'breakfast' => 'Завтрак включен',
                    ),
                    'ability' => array(
                        'reception' => 'Круглосуточная стойка регистрации',
                        'wifi' => 'Wi-Fi',
                        'free-wifi' => 'Бесплатный Wi-Fi',
                        'car-side' => 'Прокат автомобиля',
                        'taxi' => 'Заказ такси',
                        'menu' => 'Заказ еды',
                        'travelling' => 'Заказ экскурсий',
                        'lock' => 'Контроль доступа',
                        'camera' => 'Видеонаблюдение',
                        'keys' => 'Электронные замки',
                        'metro' => 'Близость метрополитена',
                        'disability' => 'Доступ для маломобильных граждан',
                        'blind' => 'Доступ для слепых граждан',
                    ),
                    'roomtype' => array(
                        'maleroom' => 'Мужская комната',
                        'femaleroom' => 'Женская комната',
                        'ruby' => 'Люкс',
                    ),
                    'furniture' => array(
                        'bed' => 'Односпальная кровать',
                        'double-bed' => 'Двуспальная кровать',
                        'sofa' => 'Диван-кровать',
                        'armchair' => 'Кресло',
                        'table' => 'Стол',
                        'platen' => 'Столик',
                        'chair' => 'Стул',
                        'dresstable' => 'Трюмо',
                        'wardrobe' => 'Платяной шкаф',
                        'bookcase' => 'Книжный шкаф',
                        'torchere' => 'Торшер',
                        'safe' => 'Сейф',
                    ),
                    'food' => array(
                        'kitchen' => 'Кухня',
                        'bar' => 'Минибар',
                        'teapot' => 'Чайник',
                        'coffee' => 'Кофе-машина',
                        'fridge' => 'Холодильник',
                    ),
                    'amenity' => array(
                        'bath' => 'Ванная',
                        'sink' => 'Умывальник',
                        'shower' => 'Душ',
                        'toilets' => 'Туалет',
                        'ws' => 'Унитаз',
                        'sign-in' => 'Запасной выход',
                    ),
                    'technical' => array(
                        'telephone' => 'Телефон',
                        'fan' => 'Вентилятор',
                        'conditioner' => 'Кондиционер',
                        'modern-tv' => 'Современный телевизор',
                        'old-tv' => 'Телевизор',
                        'homecinema' => 'Домашний кинотеатр',
                        'washmachine' => 'Стиральная машина',
                        'radio' => 'Радио',
                        'iron' => 'Утюг',
                        'plug' => 'Электророзетка',
                        'board-games' => 'Настольные игры',
                    ),
                ),
            ),
            'mbh.mbhs' => array(
                'mbhs' => 'aHR0cDovL21iaHMuaC9hcHBfZGV2LnBocC8=',
                'key' => '2743004999743693560727430049997436935607',
            ),
            'mbh.default.sources' => array(
                0 => 'Он-лайн бронирование',
                1 => 'ВашОтель.ру',
                2 => 'Постоянный клиент',
                3 => 'Рекомендация знакомых',
            ),
            'mbh.services' => array(
                'calcTypes' => array(
                    'per_stay' => 'за весь срок',
                    'per_night' => 'за cутки',
                    'not_applicable' => 'за услугу',
                    'day_percent' => 'за услугу (% от цены за сутки)',
                ),
            ),
            'mbh.mailer' => array(
                'fromText' => 'MaxiBooking',
                'fromMail' => 'robot@maxi-booking.ru',
                'subject' => 'Сообщение от MaxiBooking',
                'template' => 'MBHBaseBundle:Mailer:base.html.twig',
            ),
            'mbh.online.form' => array(
                'sites' => array(
                    0 => 'http://test.h',
                    1 => 'http://example.com',
                ),
                'packages_max' => 5,
                'messages' => true,
                'form' => array(
                    'width' => '300px',
                    'height' => 'auto',
                ),
                'result' => array(
                    'width' => 'auto',
                    'height' => 'auto',
                    'url' => 'http://test.h/hotel/results.html',
                ),
                'payment_types' => array(
                    'in_hotel' => 'Оплата при заселении',
                    'online_full' => 'Онлайн оплата (100%)',
                    'online_first_day' => 'Онлайн оплата (одни сутки)',
                    'online_half' => 'Онлайн оплата (50%)',
                ),
            ),
            'mbh.payment_systems' => array(
                'moneymail' => 'MoneyMail',
                'robokassa' => 'Robokassa',
                'payanyway' => 'PayAnyWay',
                'uniteller' => 'Uniteller',
                'rbk' => 'Rbk',
            ),
            'mbh.payment_systems.default' => 'uniteller',
            'mbh.payment_systems.change' => true,
            'mbh.channelmanager.services' => array(
                'vashotel' => array(
                    'title' => 'ВашОтель.RU',
                    'service' => 'mbh.channelmanager.vashotel',
                    'password' => 'jguuE26t684Fl',
                ),
                'booking' => array(
                    'title' => 'Booking.com',
                    'service' => 'mbh.channelmanager.booking',
                    'username' => 'maxibookingXML',
                    'password' => 'bMBhv6tds5i',
                ),
                'ostrovok' => array(
                    'title' => 'Ostrovok',
                    'service' => 'mbh.channelmanager.ostrovok',
                    'username' => '722ac50470d8af33d509c069ccb83443',
                    'password' => 'edfdfba3f6902eb63aa254935e9a8a36',
                ),
                'myallocator' => array(
                    'title' => 'MyAllocator',
                    'service' => 'mbh.channelmanager.myallocator',
                    'api_username' => 'maxipanev',
                    'api_password' => 'VCuMhanjdxiC',
                    'vendor_password' => '0Rp0XJGubJ9rfoUy4niNGcBPErf20VJZ',
                    'url' => 'http://mbhs.maxibooking.ru/client/channelmanager/push/myallocator',
                ),
            ),
            'mbh.logs.max' => 100,
            'mbh.reports.accommodation.rooms.max' => 15,
            'mbh.analytics.types' => array(
                'sales_packages' => 'Динамика продаж по количеству броней',
                'sales_cash' => 'Динамика продаж по стоимости броней',
                'sales_cash_documents' => 'Динамика продаж в полученных деньгах',
                'sales_sources' => 'Источники продаж',
                'hotel_occupancy' => 'Заполняемость отеля',
                'sales_services' => 'Динамика реализации услуг',
                'sales_managers' => 'Менеджеры и брони',
            ),
            'mbh.package.arrivals' => array(
                'service' => 'Служебная',
                'tourism' => 'Туризм',
                'business' => 'Деловая',
                'study' => 'Учеба',
                'work' => 'Работа',
                'private' => 'Частная',
                'residence' => 'Транзит',
                'humanitarian' => 'Гуманитарная',
                'other' => 'Другая',
            ),
            'mbh.package.arrival.time' => 12,
            'mbh.package.departure.time' => 14,
            'mbh.package.notpaid.time' => '-5 days',
            'mbh.package.statuses' => array(
                'offline' => array(
                    'title' => 'Обычная',
                    'class' => 'info',
                    'icon' => 'fa fa-paper-plane-o',
                ),
                'online' => array(
                    'title' => 'Онлайн',
                    'class' => 'success',
                    'icon' => 'fa fa-globe',
                ),
                'channel_manager' => array(
                    'title' => 'Channel manager',
                    'class' => 'danger',
                    'icon' => 'fa fa-cloud-download',
                ),
            ),
            'mbh.task.statuses' => array(
                'open' => array(
                    'title' => 'Открыта',
                    'class' => 'success',
                ),
                'process' => array(
                    'title' => 'В процессе',
                    'class' => 'info',
                ),
                'closed' => array(
                    'title' => 'Закрыта',
                    'class' => 'danger',
                ),
            ),
            'mbh.tasktype.priority' => array(
                0 => 'low',
                1 => 'average',
                2 => 'high',
            ),
            'mbh.order.document.types' => array(
                'passport' => 'package.document.type_passport',
                'foreign_passport' => 'package.document.type_foreign_passport',
                'insurance' => 'package.document.type_insurance',
                'birth_certificate' => 'package.document.type_birth_certificate',
                'military' => 'package.document.type_military',
                'accommodation_voucher' => 'package.document.type_accommodation_voucher',
                'shuttle_voucher' => 'package.document.type_shuttle_voucher',
                'sertives_voucher' => 'package.document.type_sertives_voucher',
                'complex' => 'package.document.type_complex',
                'congirmation_account' => 'package.document.type_congirmation_account',
                'invoice_for_payment' => 'package.document.type_invoice_for_payment',
                'contract' => 'package.document.type_contract',
                'extra_argument' => 'package.document.type_extra_argument',
                'air_tickets' => 'package.document.type_air_tickets',
                'train_tickets' => 'package.document.type_train_tickets',
                'info' => 'package.document.type_info',
                'letter' => 'package.document.type_letter',
                'other' => 'package.document.type_other',
            ),
            'mbh.cash.methods' => array(
                'cash' => 'Наличные',
                'cashless' => 'Безнал',
                'electronic' => 'Электронный',
            ),
            'mbh.cash.operations' => array(
                'in' => 'Поступление',
                'out' => 'Расход',
                'fee' => 'Комиссия',
            ),
            'mbh.gender.types' => array(
                'male' => 'Мужчина',
                'female' => 'Женщина',
                'unknown' => 'Неизвестно',
            ),
            'mbh.organization.types' => array(
                'contragents' => 'Контрагенты',
                'my' => 'Мои организации',
            ),
            'mbh.payer_organization' => array(
                'name' => 'ООО "Наличные средства"',
                'checking_account' => '44400000000000000000',
                'inn' => '7700000000',
                'kpp' => '770000000',
                'bank' => '""',
                'bank_bik' => '000000000',
                'correspondent_account' => '33300000000000000000',
                'accountant_fio' => '',
            ),
            'mbh.room_status_icons' => array(
                'repair' => 'tools',
                'cleaning' => 'clean',
                'reserve' => 'lock',
                'other' => 'clock',
            ),
            'mbh.languages' => array(
                0 => 'en',
                1 => 'ru',
            ),
            'mbh.warehouse.operations' => array(
                'in' => 'Приход',
                'out' => 'Расход',
            ),
            'mbh.units' => array(
                'per_kg' => 'unit.per_kg',
                'per_piece' => 'unit.per_piece',
                'per_grm' => 'unit.per_grm',
                'per_l' => 'unit.per_l',
                'per_ml' => 'unit.per_ml',
            ),
            'knp_menu.renderer.twig.options' => array(
                'currentClass' => 'active',
            ),
            'controller_resolver.class' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerResolver',
            'controller_name_converter.class' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\ControllerNameParser',
            'response_listener.class' => 'Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener',
            'streamed_response_listener.class' => 'Symfony\\Component\\HttpKernel\\EventListener\\StreamedResponseListener',
            'locale_listener.class' => 'Symfony\\Component\\HttpKernel\\EventListener\\LocaleListener',
            'event_dispatcher.class' => 'Symfony\\Component\\EventDispatcher\\ContainerAwareEventDispatcher',
            'http_kernel.class' => 'Symfony\\Component\\HttpKernel\\DependencyInjection\\ContainerAwareHttpKernel',
            'filesystem.class' => 'Symfony\\Component\\Filesystem\\Filesystem',
            'cache_warmer.class' => 'Symfony\\Component\\HttpKernel\\CacheWarmer\\CacheWarmerAggregate',
            'cache_clearer.class' => 'Symfony\\Component\\HttpKernel\\CacheClearer\\ChainCacheClearer',
            'file_locator.class' => 'Symfony\\Component\\HttpKernel\\Config\\FileLocator',
            'uri_signer.class' => 'Symfony\\Component\\HttpKernel\\UriSigner',
            'request_stack.class' => 'Symfony\\Component\\HttpFoundation\\RequestStack',
            'fragment.handler.class' => 'Symfony\\Component\\HttpKernel\\DependencyInjection\\LazyLoadingFragmentHandler',
            'fragment.renderer.inline.class' => 'Symfony\\Component\\HttpKernel\\Fragment\\InlineFragmentRenderer',
            'fragment.renderer.hinclude.class' => 'Symfony\\Component\\HttpKernel\\Fragment\\HIncludeFragmentRenderer',
            'fragment.renderer.hinclude.global_template' => NULL,
            'fragment.renderer.esi.class' => 'Symfony\\Component\\HttpKernel\\Fragment\\EsiFragmentRenderer',
            'fragment.path' => '/_fragment',
            'translator.class' => 'Symfony\\Bundle\\FrameworkBundle\\Translation\\Translator',
            'translator.identity.class' => 'Symfony\\Component\\Translation\\IdentityTranslator',
            'translator.selector.class' => 'Symfony\\Component\\Translation\\MessageSelector',
            'translation.loader.php.class' => 'Symfony\\Component\\Translation\\Loader\\PhpFileLoader',
            'translation.loader.yml.class' => 'Symfony\\Component\\Translation\\Loader\\YamlFileLoader',
            'translation.loader.xliff.class' => 'Symfony\\Component\\Translation\\Loader\\XliffFileLoader',
            'translation.loader.po.class' => 'Symfony\\Component\\Translation\\Loader\\PoFileLoader',
            'translation.loader.mo.class' => 'Symfony\\Component\\Translation\\Loader\\MoFileLoader',
            'translation.loader.qt.class' => 'Symfony\\Component\\Translation\\Loader\\QtFileLoader',
            'translation.loader.csv.class' => 'Symfony\\Component\\Translation\\Loader\\CsvFileLoader',
            'translation.loader.res.class' => 'Symfony\\Component\\Translation\\Loader\\IcuResFileLoader',
            'translation.loader.dat.class' => 'Symfony\\Component\\Translation\\Loader\\IcuDatFileLoader',
            'translation.loader.ini.class' => 'Symfony\\Component\\Translation\\Loader\\IniFileLoader',
            'translation.loader.json.class' => 'Symfony\\Component\\Translation\\Loader\\JsonFileLoader',
            'translation.dumper.php.class' => 'Symfony\\Component\\Translation\\Dumper\\PhpFileDumper',
            'translation.dumper.xliff.class' => 'Symfony\\Component\\Translation\\Dumper\\XliffFileDumper',
            'translation.dumper.po.class' => 'Symfony\\Component\\Translation\\Dumper\\PoFileDumper',
            'translation.dumper.mo.class' => 'Symfony\\Component\\Translation\\Dumper\\MoFileDumper',
            'translation.dumper.yml.class' => 'Symfony\\Component\\Translation\\Dumper\\YamlFileDumper',
            'translation.dumper.qt.class' => 'Symfony\\Component\\Translation\\Dumper\\QtFileDumper',
            'translation.dumper.csv.class' => 'Symfony\\Component\\Translation\\Dumper\\CsvFileDumper',
            'translation.dumper.ini.class' => 'Symfony\\Component\\Translation\\Dumper\\IniFileDumper',
            'translation.dumper.json.class' => 'Symfony\\Component\\Translation\\Dumper\\JsonFileDumper',
            'translation.dumper.res.class' => 'Symfony\\Component\\Translation\\Dumper\\IcuResFileDumper',
            'translation.extractor.php.class' => 'Symfony\\Bundle\\FrameworkBundle\\Translation\\PhpExtractor',
            'translation.loader.class' => 'Symfony\\Bundle\\FrameworkBundle\\Translation\\TranslationLoader',
            'translation.extractor.class' => 'Symfony\\Component\\Translation\\Extractor\\ChainExtractor',
            'translation.writer.class' => 'Symfony\\Component\\Translation\\Writer\\TranslationWriter',
            'property_accessor.class' => 'Symfony\\Component\\PropertyAccess\\PropertyAccessor',
            'kernel.secret' => 'mySyperSecretKeyForSymfony',
            'kernel.http_method_override' => true,
            'kernel.trusted_hosts' => array(

            ),
            'kernel.trusted_proxies' => array(
                0 => '127.0.0.1',
                1 => '127.0.1.1',
                2 => '176.192.20.30',
            ),
            'kernel.default_locale' => 'ru',
            'session.class' => 'Symfony\\Component\\HttpFoundation\\Session\\Session',
            'session.flashbag.class' => 'Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBag',
            'session.attribute_bag.class' => 'Symfony\\Component\\HttpFoundation\\Session\\Attribute\\AttributeBag',
            'session.storage.metadata_bag.class' => 'Symfony\\Component\\HttpFoundation\\Session\\Storage\\MetadataBag',
            'session.metadata.storage_key' => '_sf2_meta',
            'session.storage.native.class' => 'Symfony\\Component\\HttpFoundation\\Session\\Storage\\NativeSessionStorage',
            'session.storage.php_bridge.class' => 'Symfony\\Component\\HttpFoundation\\Session\\Storage\\PhpBridgeSessionStorage',
            'session.storage.mock_file.class' => 'Symfony\\Component\\HttpFoundation\\Session\\Storage\\MockFileSessionStorage',
            'session.handler.native_file.class' => 'Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\NativeFileSessionHandler',
            'session.handler.write_check.class' => 'Symfony\\Component\\HttpFoundation\\Session\\Storage\\Handler\\WriteCheckSessionHandler',
            'session_listener.class' => 'Symfony\\Bundle\\FrameworkBundle\\EventListener\\SessionListener',
            'session.storage.options' => array(
                'cookie_httponly' => true,
                'gc_probability' => 1,
            ),
            'session.save_path' => (__DIR__.'/sessions'),
            'session.metadata.update_threshold' => '0',
            'security.secure_random.class' => 'Symfony\\Component\\Security\\Core\\Util\\SecureRandom',
            'form.resolved_type_factory.class' => 'Symfony\\Component\\Form\\ResolvedFormTypeFactory',
            'form.registry.class' => 'Symfony\\Component\\Form\\FormRegistry',
            'form.factory.class' => 'Symfony\\Component\\Form\\FormFactory',
            'form.extension.class' => 'Symfony\\Component\\Form\\Extension\\DependencyInjection\\DependencyInjectionExtension',
            'form.type_guesser.validator.class' => 'Symfony\\Component\\Form\\Extension\\Validator\\ValidatorTypeGuesser',
            'form.type_extension.form.request_handler.class' => 'Symfony\\Component\\Form\\Extension\\HttpFoundation\\HttpFoundationRequestHandler',
            'form.type_extension.csrf.enabled' => true,
            'form.type_extension.csrf.field_name' => '_token',
            'security.csrf.token_generator.class' => 'Symfony\\Component\\Security\\Csrf\\TokenGenerator\\UriSafeTokenGenerator',
            'security.csrf.token_storage.class' => 'Symfony\\Component\\Security\\Csrf\\TokenStorage\\SessionTokenStorage',
            'security.csrf.token_manager.class' => 'Symfony\\Component\\Security\\Csrf\\CsrfTokenManager',
            'templating.engine.delegating.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\DelegatingEngine',
            'templating.name_parser.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\TemplateNameParser',
            'templating.filename_parser.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\TemplateFilenameParser',
            'templating.cache_warmer.template_paths.class' => 'Symfony\\Bundle\\FrameworkBundle\\CacheWarmer\\TemplatePathsCacheWarmer',
            'templating.locator.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Loader\\TemplateLocator',
            'templating.loader.filesystem.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Loader\\FilesystemLoader',
            'templating.loader.cache.class' => 'Symfony\\Component\\Templating\\Loader\\CacheLoader',
            'templating.loader.chain.class' => 'Symfony\\Component\\Templating\\Loader\\ChainLoader',
            'templating.finder.class' => 'Symfony\\Bundle\\FrameworkBundle\\CacheWarmer\\TemplateFinder',
            'templating.helper.assets.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\AssetsHelper',
            'templating.helper.router.class' => 'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\RouterHelper',
            'templating.helper.code.file_link_format' => NULL,
            'templating.loader.cache.path' => NULL,
            'templating.engines' => array(
                0 => 'twig',
            ),
            'validator.class' => 'Symfony\\Component\\Validator\\Validator\\ValidatorInterface',
            'validator.builder.class' => 'Symfony\\Component\\Validator\\ValidatorBuilderInterface',
            'validator.builder.factory.class' => 'Symfony\\Component\\Validator\\Validation',
            'validator.mapping.cache.apc.class' => 'Symfony\\Component\\Validator\\Mapping\\Cache\\ApcCache',
            'validator.mapping.cache.prefix' => '',
            'validator.validator_factory.class' => 'Symfony\\Bundle\\FrameworkBundle\\Validator\\ConstraintValidatorFactory',
            'validator.expression.class' => 'Symfony\\Component\\Validator\\Constraints\\ExpressionValidator',
            'validator.email.class' => 'Symfony\\Component\\Validator\\Constraints\\EmailValidator',
            'validator.translation_domain' => 'validators',
            'validator.api' => '2.5-bc',
            'fragment.listener.class' => 'Symfony\\Component\\HttpKernel\\EventListener\\FragmentListener',
            'translator.logging' => true,
            'profiler.class' => 'Symfony\\Component\\HttpKernel\\Profiler\\Profiler',
            'profiler_listener.class' => 'Symfony\\Component\\HttpKernel\\EventListener\\ProfilerListener',
            'data_collector.config.class' => 'Symfony\\Component\\HttpKernel\\DataCollector\\ConfigDataCollector',
            'data_collector.request.class' => 'Symfony\\Component\\HttpKernel\\DataCollector\\RequestDataCollector',
            'data_collector.exception.class' => 'Symfony\\Component\\HttpKernel\\DataCollector\\ExceptionDataCollector',
            'data_collector.events.class' => 'Symfony\\Component\\HttpKernel\\DataCollector\\EventDataCollector',
            'data_collector.logger.class' => 'Symfony\\Component\\HttpKernel\\DataCollector\\LoggerDataCollector',
            'data_collector.time.class' => 'Symfony\\Component\\HttpKernel\\DataCollector\\TimeDataCollector',
            'data_collector.memory.class' => 'Symfony\\Component\\HttpKernel\\DataCollector\\MemoryDataCollector',
            'data_collector.router.class' => 'Symfony\\Bundle\\FrameworkBundle\\DataCollector\\RouterDataCollector',
            'form.resolved_type_factory.data_collector_proxy.class' => 'Symfony\\Component\\Form\\Extension\\DataCollector\\Proxy\\ResolvedTypeFactoryDataCollectorProxy',
            'form.type_extension.form.data_collector.class' => 'Symfony\\Component\\Form\\Extension\\DataCollector\\Type\\DataCollectorTypeExtension',
            'data_collector.form.class' => 'Symfony\\Component\\Form\\Extension\\DataCollector\\FormDataCollector',
            'data_collector.form.extractor.class' => 'Symfony\\Component\\Form\\Extension\\DataCollector\\FormDataExtractor',
            'profiler_listener.only_exceptions' => false,
            'profiler_listener.only_master_requests' => false,
            'profiler.storage.dsn' => ('file:'.__DIR__.'/profiler'),
            'profiler.storage.username' => '',
            'profiler.storage.password' => '',
            'profiler.storage.lifetime' => 86400,
            'router.class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\Router',
            'router.request_context.class' => 'Symfony\\Component\\Routing\\RequestContext',
            'routing.loader.class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\DelegatingLoader',
            'routing.resolver.class' => 'Symfony\\Component\\Config\\Loader\\LoaderResolver',
            'routing.loader.xml.class' => 'Symfony\\Component\\Routing\\Loader\\XmlFileLoader',
            'routing.loader.yml.class' => 'Symfony\\Component\\Routing\\Loader\\YamlFileLoader',
            'routing.loader.php.class' => 'Symfony\\Component\\Routing\\Loader\\PhpFileLoader',
            'router.options.generator_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'router.options.generator_base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'router.options.generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper',
            'router.options.matcher_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher',
            'router.options.matcher_base_class' => 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher',
            'router.options.matcher_dumper_class' => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper',
            'router.cache_warmer.class' => 'Symfony\\Bundle\\FrameworkBundle\\CacheWarmer\\RouterCacheWarmer',
            'router.options.matcher.cache_class' => 'appDevUrlMatcher',
            'router.options.generator.cache_class' => 'appDevUrlGenerator',
            'router_listener.class' => 'Symfony\\Component\\HttpKernel\\EventListener\\RouterListener',
            'router.resource' => ($this->targetDirs[2].'/config/routing_dev.yml'),
            'router.cache_class_prefix' => 'appDev',
            'request_listener.http_port' => 80,
            'request_listener.https_port' => 443,
            'annotations.reader.class' => 'Doctrine\\Common\\Annotations\\AnnotationReader',
            'annotations.cached_reader.class' => 'Doctrine\\Common\\Annotations\\CachedReader',
            'annotations.file_cache_reader.class' => 'Doctrine\\Common\\Annotations\\FileCacheReader',
            'serializer.class' => 'Symfony\\Component\\Serializer\\Serializer',
            'serializer.encoder.xml.class' => 'Symfony\\Component\\Serializer\\Encoder\\XmlEncoder',
            'serializer.encoder.json.class' => 'Symfony\\Component\\Serializer\\Encoder\\JsonEncoder',
            'serializer.mapping.cache.prefix' => '',
            'debug.debug_handlers_listener.class' => 'Symfony\\Component\\HttpKernel\\EventListener\\DebugHandlersListener',
            'debug.stopwatch.class' => 'Symfony\\Component\\Stopwatch\\Stopwatch',
            'debug.error_handler.throw_at' => -1,
            'debug.event_dispatcher.class' => 'Symfony\\Component\\HttpKernel\\Debug\\TraceableEventDispatcher',
            'debug.container.dump' => (__DIR__.'/appDevDebugProjectContainer.xml'),
            'debug.controller_resolver.class' => 'Symfony\\Component\\HttpKernel\\Controller\\TraceableControllerResolver',
            'security.context.class' => 'Symfony\\Component\\Security\\Core\\SecurityContext',
            'security.user_checker.class' => 'Symfony\\Component\\Security\\Core\\User\\UserChecker',
            'security.encoder_factory.generic.class' => 'Symfony\\Component\\Security\\Core\\Encoder\\EncoderFactory',
            'security.encoder.digest.class' => 'Symfony\\Component\\Security\\Core\\Encoder\\MessageDigestPasswordEncoder',
            'security.encoder.plain.class' => 'Symfony\\Component\\Security\\Core\\Encoder\\PlaintextPasswordEncoder',
            'security.encoder.pbkdf2.class' => 'Symfony\\Component\\Security\\Core\\Encoder\\Pbkdf2PasswordEncoder',
            'security.encoder.bcrypt.class' => 'Symfony\\Component\\Security\\Core\\Encoder\\BCryptPasswordEncoder',
            'security.user.provider.in_memory.class' => 'Symfony\\Component\\Security\\Core\\User\\InMemoryUserProvider',
            'security.user.provider.in_memory.user.class' => 'Symfony\\Component\\Security\\Core\\User\\User',
            'security.user.provider.chain.class' => 'Symfony\\Component\\Security\\Core\\User\\ChainUserProvider',
            'security.authentication.trust_resolver.class' => 'Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationTrustResolver',
            'security.authentication.trust_resolver.anonymous_class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\AnonymousToken',
            'security.authentication.trust_resolver.rememberme_class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken',
            'security.authentication.manager.class' => 'Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationProviderManager',
            'security.authentication.session_strategy.class' => 'Symfony\\Component\\Security\\Http\\Session\\SessionAuthenticationStrategy',
            'security.access.decision_manager.class' => 'Symfony\\Component\\Security\\Core\\Authorization\\AccessDecisionManager',
            'security.access.simple_role_voter.class' => 'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\RoleVoter',
            'security.access.authenticated_voter.class' => 'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AuthenticatedVoter',
            'security.access.role_hierarchy_voter.class' => 'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\RoleHierarchyVoter',
            'security.access.expression_voter.class' => 'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\ExpressionVoter',
            'security.firewall.class' => 'Symfony\\Component\\Security\\Http\\Firewall',
            'security.firewall.map.class' => 'Symfony\\Bundle\\SecurityBundle\\Security\\FirewallMap',
            'security.firewall.context.class' => 'Symfony\\Bundle\\SecurityBundle\\Security\\FirewallContext',
            'security.matcher.class' => 'Symfony\\Component\\HttpFoundation\\RequestMatcher',
            'security.expression_matcher.class' => 'Symfony\\Component\\HttpFoundation\\ExpressionRequestMatcher',
            'security.role_hierarchy.class' => 'Symfony\\Component\\Security\\Core\\Role\\RoleHierarchy',
            'security.http_utils.class' => 'Symfony\\Component\\Security\\Http\\HttpUtils',
            'security.validator.user_password.class' => 'Symfony\\Component\\Security\\Core\\Validator\\Constraints\\UserPasswordValidator',
            'security.expression_language.class' => 'Symfony\\Component\\Security\\Core\\Authorization\\ExpressionLanguage',
            'security.role_hierarchy.roles' => array(
                'ROLE_SUPER_ADMIN' => array(
                    0 => 'ROLE_ADMIN',
                ),
                'ROLE_ADMIN' => array(
                    0 => 'ROLE_HOTEL',
                    1 => 'ROLE_GROUP',
                    2 => 'ROLE_CITY',
                    3 => 'ROLE_LOGS',
                    4 => 'ROLE_CASH',
                    5 => 'ROLE_CLIENT_CONFIG',
                    6 => 'ROLE_DOCUMENT_TEMPLATES',
                    7 => 'ROLE_HOUSING',
                    8 => 'ROLE_ROOM',
                    9 => 'ROLE_ROOM_TYPE',
                    10 => 'ROLE_TASK_MANAGER',
                    11 => 'ROLE_MANAGER',
                    12 => 'ROLE_OVERVIEW',
                    13 => 'ROLE_PRICE_CACHE',
                    14 => 'ROLE_RESTRICTION',
                    15 => 'ROLE_ROOM_CACHE',
                    16 => 'ROLE_SERVICE',
                    17 => 'ROLE_SERVICE_CATEGORY',
                    18 => 'ROLE_TARIFF',
                    19 => 'ROLE_USER',
                    20 => 'ROLE_CHANNEL_MANAGER',
                    21 => 'ROLE_ONLINE_FORM',
                    22 => 'ROLE_POLLS',
                    23 => 'ROLE_REPORTS',
                    24 => 'ROLE_PACKAGE',
                    25 => 'ROLE_SOURCE',
                    26 => 'ROLE_PROMOTION',
                    27 => 'ROLE_ROOM_TYPE_CATEGORY',
                    28 => 'ROLE_WORK_SHIFT',
                    29 => 'ROLE_WAREHOUSE',
                    30 => 'ROLE_RESTAURANT',
                ),
                'ROLE_LOGS' => array(
                    0 => 'ROLE_BASE_USER',
                ),
                'ROLE_USER' => array(
                    0 => 'ROLE_USER_VIEW',
                    1 => 'ROLE_USER_NEW',
                    2 => 'ROLE_USER_EDIT',
                    3 => 'ROLE_USER_DELETE',
                    4 => 'ROLE_USER_PROFILE',
                ),
                'ROLE_GROUP' => array(
                    0 => 'ROLE_GROUP_VIEW',
                    1 => 'ROLE_GROUP_NEW',
                    2 => 'ROLE_GROUP_EDIT',
                    3 => 'ROLE_GROUP_DELETE',
                ),
                'ROLE_HOTEL' => array(
                    0 => 'ROLE_HOTEL_VIEW',
                    1 => 'ROLE_HOTEL_NEW',
                    2 => 'ROLE_HOTEL_EDIT',
                    3 => 'ROLE_HOTEL_DELETE',
                ),
                'ROLE_CITY' => array(
                    0 => 'ROLE_CITY_VIEW',
                ),
                'ROLE_CASH' => array(
                    0 => 'ROLE_CASH_VIEW',
                    1 => 'ROLE_CASH_NEW',
                    2 => 'ROLE_CASH_EDIT',
                    3 => 'ROLE_CASH_DELETE',
                    4 => 'ROLE_CASH_CONFIRM',
                    5 => 'ROLE_CASH_NUMBER',
                ),
                'ROLE_CLIENT_CONFIG' => array(
                    0 => 'ROLE_CLIENT_CONFIG_VIEW',
                    1 => 'ROLE_CLIENT_CONFIG_EDIT',
                ),
                'ROLE_DOCUMENT_TEMPLATES' => array(
                    0 => 'ROLE_DOCUMENT_TEMPLATES_VIEW',
                    1 => 'ROLE_DOCUMENT_TEMPLATES_NEW',
                    2 => 'ROLE_DOCUMENT_TEMPLATES_EDIT',
                    3 => 'ROLE_DOCUMENT_TEMPLATES_DELETE',
                ),
                'ROLE_ROOM_FUND' => array(
                    0 => 'ROLE_ROOM_TYPE',
                    1 => 'ROLE_ROOM',
                ),
                'ROLE_HOUSING' => array(
                    0 => 'ROLE_HOUSING_VIEW',
                    1 => 'ROLE_HOUSING_NEW',
                    2 => 'ROLE_HOUSING_EDIT',
                    3 => 'ROLE_HOUSING_DELETE',
                ),
                'ROLE_ROOM_TYPE' => array(
                    0 => 'ROLE_ROOM_TYPE_VIEW',
                    1 => 'ROLE_ROOM_TYPE_NEW',
                    2 => 'ROLE_ROOM_TYPE_EDIT',
                    3 => 'ROLE_ROOM_TYPE_DELETE',
                ),
                'ROLE_ROOM' => array(
                    0 => 'ROLE_ROOM_VIEW',
                    1 => 'ROLE_ROOM_NEW',
                    2 => 'ROLE_ROOM_EDIT',
                    3 => 'ROLE_ROOM_DELETE',
                    4 => 'ROLE_ROOM_STATUS_EDIT',
                ),
                'ROLE_CHANNEL_MANAGER' => array(
                    0 => 'ROLE_BOOKING',
                    1 => 'ROLE_VASHOTEL',
                    2 => 'ROLE_OSTROVOK',
                    3 => 'ROLE_OKTOGO',
                    4 => 'ROLE_HOTELINN',
                    5 => 'ROLE_MYALLOCATOR',
                ),
                'ROLE_ONLINE_FORM' => array(
                    0 => 'ROLE_ONLINE_FORM_VIEW',
                    1 => 'ROLE_ONLINE_FORM_NEW',
                    2 => 'ROLE_ONLINE_FORM_EDIT',
                    3 => 'ROLE_ONLINE_FORM_DELETE',
                ),
                'ROLE_POLLS' => array(
                    0 => 'ROLE_BASE_USER',
                ),
                'ROLE_PACKAGE' => array(
                    0 => 'ROLE_SEARCH',
                    1 => 'ROLE_PACKAGE_VIEW',
                    2 => 'ROLE_PACKAGE_NEW',
                    3 => 'ROLE_ORDER_EDIT',
                    4 => 'ROLE_PACKAGE_EDIT',
                    5 => 'ROLE_ORDER_PAYER',
                    6 => 'ROLE_PACKAGE_GUESTS',
                    7 => 'ROLE_PACKAGE_SERVICES',
                    8 => 'ROLE_PACKAGE_ACCOMMODATION',
                    9 => 'ROLE_ORDER_DOCUMENTS',
                    10 => 'ROLE_DOCUMENTS_GENERATOR',
                    11 => 'ROLE_ORDER_CASH_DOCUMENTS',
                    12 => 'ROLE_PACKAGE_DELETE',
                    13 => 'ROLE_PACKAGE_VIEW_ALL',
                    14 => 'ROLE_PACKAGE_EDIT_ALL',
                    15 => 'ROLE_PACKAGE_DELETE_ALL',
                    16 => 'ROLE_PACKAGE_DOCS',
                    17 => 'ROLE_ORDER_AUTO_CONFIRMATION',
                    18 => 'ROLE_INDIVIDUAL_PROMOTION_ADD',
                    19 => 'ROLE_PROMOTION_ADD',
                    20 => 'ROLE_DISCOUNT_ADD',
                    21 => 'ROLE_PACKAGE_PRICE_EDIT',
                    22 => 'ROLE_FORCE_BOOKING',
                ),
                'ROLE_PACKAGE_VIEW_ALL' => array(
                    0 => 'ROLE_PACKAGE_VIEW',
                ),
                'ROLE_PACKAGE_DELETE_ALL' => array(
                    0 => 'ROLE_PACKAGE_DELETE',
                ),
                'ROLE_REPORTS' => array(
                    0 => 'ROLE_ANALYTICS',
                    1 => 'ROLE_TOURIST_REPORT',
                    2 => 'ROLE_PORTER_REPORT',
                    3 => 'ROLE_ACCOMMODATION_REPORT',
                    4 => 'ROLE_SERVICES_REPORT',
                    5 => 'ROLE_ORGANIZATION',
                    6 => 'ROLE_MANAGERS_REPORT',
                    7 => 'ROLE_POLLS_REPORT',
                    8 => 'ROLE_ROOMS_REPORT',
                ),
                'ROLE_TOURIST_REPORT' => array(
                    0 => 'ROLE_TOURIST',
                ),
                'ROLE_TOURIST' => array(
                    0 => 'ROLE_TOURIST_VIEW',
                    1 => 'ROLE_TOURIST_NEW',
                    2 => 'ROLE_TOURIST_EDIT',
                    3 => 'ROLE_TOURIST_DELETE',
                ),
                'ROLE_ORGANIZATION' => array(
                    0 => 'ROLE_ORGANIZATION_VIEW',
                    1 => 'ROLE_ORGANIZATION_NEW',
                    2 => 'ROLE_ORGANIZATION_EDIT',
                    3 => 'ROLE_ORGANIZATION_DELETE',
                ),
                'ROLE_SOURCE' => array(
                    0 => 'ROLE_SOURCE_VIEW',
                    1 => 'ROLE_SOURCE_NEW',
                    2 => 'ROLE_SOURCE_EDIT',
                    3 => 'ROLE_SOURCE_DELETE',
                ),
                'ROLE_TASK_MANAGER' => array(
                    0 => 'ROLE_TASK',
                    1 => 'ROLE_TASK_TYPE',
                    2 => 'ROLE_TASK_TYPE_CATEGORY',
                ),
                'ROLE_TASK' => array(
                    0 => 'ROLE_TASK_VIEW',
                    1 => 'ROLE_TASK_OWN_VIEW',
                    2 => 'ROLE_TASK_NEW',
                    3 => 'ROLE_TASK_EDIT',
                    4 => 'ROLE_TASK_DELETE',
                ),
                'ROLE_TASK_TYPE' => array(
                    0 => 'ROLE_TASK_TYPE_VIEW',
                    1 => 'ROLE_TASK_TYPE_NEW',
                    2 => 'ROLE_TASK_TYPE_EDIT',
                    3 => 'ROLE_TASK_TYPE_DELETE',
                ),
                'ROLE_TASK_TYPE_CATEGORY' => array(
                    0 => 'ROLE_TASK_TYPE_CATEGORY_VIEW',
                    1 => 'ROLE_TASK_TYPE_CATEGORY_NEW',
                    2 => 'ROLE_TASK_TYPE_CATEGORY_EDIT',
                    3 => 'ROLE_TASK_TYPE_CATEGORY_DELETE',
                ),
                'ROLE_PRICE_CACHE' => array(
                    0 => 'ROLE_PRICE_CACHE_VIEW',
                    1 => 'ROLE_PRICE_CACHE_EDIT',
                    2 => 'ROLE_OVERVIEW',
                ),
                'ROLE_RESTRICTION' => array(
                    0 => 'ROLE_RESTRICTION_VIEW',
                    1 => 'ROLE_RESTRICTION_EDIT',
                    2 => 'ROLE_OVERVIEW',
                ),
                'ROLE_ROOM_CACHE' => array(
                    0 => 'ROLE_ROOM_CACHE_VIEW',
                    1 => 'ROLE_ROOM_CACHE_EDIT',
                    2 => 'ROLE_OVERVIEW',
                ),
                'ROLE_SERVICE' => array(
                    0 => 'ROLE_SERVICE_VIEW',
                    1 => 'ROLE_SERVICE_NEW',
                    2 => 'ROLE_SERVICE_EDIT',
                    3 => 'ROLE_SERVICE_DELETE',
                ),
                'ROLE_SERVICE_CATEGORY' => array(
                    0 => 'ROLE_SERVICE_CATEGORY_NEW',
                    1 => 'ROLE_SERVICE_CATEGORY_EDIT',
                    2 => 'ROLE_SERVICE_CATEGORY_DELETE',
                ),
                'ROLE_TARIFF' => array(
                    0 => 'ROLE_TARIFF_VIEW',
                    1 => 'ROLE_TARIFF_NEW',
                    2 => 'ROLE_TARIFF_EDIT',
                    3 => 'ROLE_TARIFF_DELETE',
                ),
                'ROLE_PROMOTION' => array(
                    0 => 'ROLE_PROMOTION_VIEW',
                    1 => 'ROLE_PROMOTION_NEW',
                    2 => 'ROLE_PROMOTION_EDIT',
                    3 => 'ROLE_PROMOTION_DELETE',
                ),
                'ROLE_ROOM_TYPE_CATEGORY' => array(
                    0 => 'ROLE_ROOM_TYPE_CATEGORY_VIEW',
                    1 => 'ROLE_ROOM_TYPE_CATEGORY_NEW',
                    2 => 'ROLE_ROOM_TYPE_CATEGORY_EDIT',
                    3 => 'ROLE_ROOM_TYPE_CATEGORY_DELETE',
                ),
                'ROLE_WORK_SHIFT' => array(
                    0 => 'ROLE_WORK_SHIFT_VIEW',
                    1 => 'ROLE_WORK_SHIFT_CLOSE',
                ),
                'ROLE_STAFF' => array(
                    0 => 'ROLE_TASK_OWN_VIEW',
                ),
                'ROLE_WAREHOUSE' => array(
                    0 => 'ROLE_WAREHOUSE_CAT',
                    1 => 'ROLE_WAREHOUSE_ITEMS',
                    2 => 'ROLE_WAREHOUSE_RECORD',
                    3 => 'ROLE_WAREHOUSE_INVOICE',
                ),
                'ROLE_WAREHOUSE_CAT' => array(
                    0 => 'ROLE_WAREHOUSE_CAT_VIEW',
                    1 => 'ROLE_WAREHOUSE_CAT_NEW',
                    2 => 'ROLE_WAREHOUSE_CAT_EDIT',
                    3 => 'ROLE_WAREHOUSE_CAT_DELETE',
                ),
                'ROLE_WAREHOUSE_ITEMS' => array(
                    0 => 'ROLE_WAREHOUSE_ITEMS_VIEW',
                    1 => 'ROLE_WAREHOUSE_ITEMS_NEW',
                    2 => 'ROLE_WAREHOUSE_ITEMS_EDIT',
                    3 => 'ROLE_WAREHOUSE_ITEMS_DELETE',
                ),
                'ROLE_WAREHOUSE_RECORD' => array(
                    0 => 'ROLE_WAREHOUSE_RECORD_VIEW',
                    1 => 'ROLE_WAREHOUSE_RECORD_NEW',
                    2 => 'ROLE_WAREHOUSE_RECORD_EDIT',
                    3 => 'ROLE_WAREHOUSE_RECORD_DELETE',
                ),
                'ROLE_WAREHOUSE_INVOICE' => array(
                    0 => 'ROLE_WAREHOUSE_INVOICE_VIEW',
                    1 => 'ROLE_WAREHOUSE_INVOICE_NEW',
                    2 => 'ROLE_WAREHOUSE_INVOICE_EDIT',
                    3 => 'ROLE_WAREHOUSE_INVOICE_DELETE',
                ),
                'ROLE_RESTAURANT' => array(
                    0 => 'ROLE_RESTAURANT_CATEGORY',
                    1 => 'ROLE_RESTAURANT_INGREDIENT',
                    2 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY',
                    3 => 'ROLE_RESTAURANT_DISHMENU_ITEM',
                    4 => 'ROLE_RESTAURANT_ORDER_MANAGER',
                    5 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED',
                    6 => 'ROLE_RESTAURANT_TABLE',
                ),
                'ROLE_RESTAURANT_CATEGORY' => array(
                    0 => 'ROLE_RESTAURANT_CATEGORY_NEW',
                    1 => 'ROLE_RESTAURANT_CATEGORY_EDIT',
                    2 => 'ROLE_RESTAURANT_CATEGORY_DELETE',
                ),
                'ROLE_RESTAURANT_INGREDIENT' => array(
                    0 => 'ROLE_RESTAURANT_INGREDIENT_VIEW',
                    1 => 'ROLE_RESTAURANT_INGREDIENT_NEW',
                    2 => 'ROLE_RESTAURANT_INGREDIENT_EDIT',
                    3 => 'ROLE_RESTAURANT_INGREDIENT_DELETE',
                ),
                'ROLE_RESTAURANT_DISHMENU_CATEGORY' => array(
                    0 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_NEW',
                    1 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_EDIT',
                    2 => 'ROLE_RESTAURANT_DISHMENU_CATEGORY_DELETE',
                ),
                'ROLE_RESTAURANT_DISHMENU_ITEM' => array(
                    0 => 'ROLE_RESTAURANT_DISHMENU_ITEM_VIEW',
                    1 => 'ROLE_RESTAURANT_DISHMENU_ITEM_NEW',
                    2 => 'ROLE_RESTAURANT_DISHMENU_ITEM_EDIT',
                    3 => 'ROLE_RESTAURANT_DISHMENU_ITEM_DELETE',
                ),
                'ROLE_RESTAURANT_ORDER_MANAGER' => array(
                    0 => 'ROLE_RESTAURANT_ORDER_MANAGER_VIEW',
                    1 => 'ROLE_RESTAURANT_ORDER_MANAGER_NEW',
                    2 => 'ROLE_RESTAURANT_ORDER_MANAGER_EDIT',
                    3 => 'ROLE_RESTAURANT_ORDER_MANAGER_DELETE',
                    4 => 'ROLE_RESTAURANT_ORDER_MANAGER_PAY',
                ),
                'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED' => array(
                    0 => 'ROLE_RESTAURANT_ORDER_MANAGER',
                    1 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_EDIT',
                    2 => 'ROLE_RESTAURANT_ORDER_MANAGER_FREEZED_DELETE',
                ),
                'ROLE_RESTAURANT_TABLE' => array(
                    0 => 'ROLE_RESTAURANT_TABLE_VIEW',
                    1 => 'ROLE_RESTAURANT_TABLE_NEW',
                    2 => 'ROLE_RESTAURANT_TABLE_EDIT',
                    3 => 'ROLE_RESTAURANT_TABLE_DELETE',
                ),
            ),
            'security.authentication.retry_entry_point.class' => 'Symfony\\Component\\Security\\Http\\EntryPoint\\RetryAuthenticationEntryPoint',
            'security.channel_listener.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\ChannelListener',
            'security.authentication.form_entry_point.class' => 'Symfony\\Component\\Security\\Http\\EntryPoint\\FormAuthenticationEntryPoint',
            'security.authentication.listener.form.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\UsernamePasswordFormAuthenticationListener',
            'security.authentication.listener.simple_form.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\SimpleFormAuthenticationListener',
            'security.authentication.listener.simple_preauth.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\SimplePreAuthenticationListener',
            'security.authentication.listener.basic.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\BasicAuthenticationListener',
            'security.authentication.basic_entry_point.class' => 'Symfony\\Component\\Security\\Http\\EntryPoint\\BasicAuthenticationEntryPoint',
            'security.authentication.listener.digest.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\DigestAuthenticationListener',
            'security.authentication.digest_entry_point.class' => 'Symfony\\Component\\Security\\Http\\EntryPoint\\DigestAuthenticationEntryPoint',
            'security.authentication.listener.x509.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\X509AuthenticationListener',
            'security.authentication.listener.anonymous.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\AnonymousAuthenticationListener',
            'security.authentication.switchuser_listener.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\SwitchUserListener',
            'security.logout_listener.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\LogoutListener',
            'security.logout.handler.session.class' => 'Symfony\\Component\\Security\\Http\\Logout\\SessionLogoutHandler',
            'security.logout.handler.cookie_clearing.class' => 'Symfony\\Component\\Security\\Http\\Logout\\CookieClearingLogoutHandler',
            'security.logout.success_handler.class' => 'Symfony\\Component\\Security\\Http\\Logout\\DefaultLogoutSuccessHandler',
            'security.access_listener.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\AccessListener',
            'security.access_map.class' => 'Symfony\\Component\\Security\\Http\\AccessMap',
            'security.exception_listener.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\ExceptionListener',
            'security.context_listener.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\ContextListener',
            'security.authentication.provider.dao.class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Provider\\DaoAuthenticationProvider',
            'security.authentication.provider.simple.class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Provider\\SimpleAuthenticationProvider',
            'security.authentication.provider.pre_authenticated.class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Provider\\PreAuthenticatedAuthenticationProvider',
            'security.authentication.provider.anonymous.class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Provider\\AnonymousAuthenticationProvider',
            'security.authentication.success_handler.class' => 'Symfony\\Component\\Security\\Http\\Authentication\\DefaultAuthenticationSuccessHandler',
            'security.authentication.failure_handler.class' => 'Symfony\\Component\\Security\\Http\\Authentication\\DefaultAuthenticationFailureHandler',
            'security.authentication.simple_success_failure_handler.class' => 'Symfony\\Component\\Security\\Http\\Authentication\\SimpleAuthenticationHandler',
            'security.authentication.provider.rememberme.class' => 'Symfony\\Component\\Security\\Core\\Authentication\\Provider\\RememberMeAuthenticationProvider',
            'security.authentication.listener.rememberme.class' => 'Symfony\\Component\\Security\\Http\\Firewall\\RememberMeListener',
            'security.rememberme.token.provider.in_memory.class' => 'Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\InMemoryTokenProvider',
            'security.authentication.rememberme.services.persistent.class' => 'Symfony\\Component\\Security\\Http\\RememberMe\\PersistentTokenBasedRememberMeServices',
            'security.authentication.rememberme.services.simplehash.class' => 'Symfony\\Component\\Security\\Http\\RememberMe\\TokenBasedRememberMeServices',
            'security.rememberme.response_listener.class' => 'Symfony\\Component\\Security\\Http\\RememberMe\\ResponseListener',
            'templating.helper.logout_url.class' => 'Symfony\\Bundle\\SecurityBundle\\Templating\\Helper\\LogoutUrlHelper',
            'templating.helper.security.class' => 'Symfony\\Bundle\\SecurityBundle\\Templating\\Helper\\SecurityHelper',
            'twig.extension.logout_url.class' => 'Symfony\\Bridge\\Twig\\Extension\\LogoutUrlExtension',
            'twig.extension.security.class' => 'Symfony\\Bridge\\Twig\\Extension\\SecurityExtension',
            'data_collector.security.class' => 'Symfony\\Bundle\\SecurityBundle\\DataCollector\\SecurityDataCollector',
            'security.access.denied_url' => NULL,
            'security.authentication.manager.erase_credentials' => true,
            'security.authentication.session_strategy.strategy' => 'migrate',
            'security.access.always_authenticate_before_granting' => false,
            'security.authentication.hide_user_not_found' => true,
            'security.acl.permission_granting_strategy.class' => 'Symfony\\Component\\Security\\Acl\\Domain\\PermissionGrantingStrategy',
            'security.acl.voter.class' => 'Symfony\\Component\\Security\\Acl\\Voter\\AclVoter',
            'security.acl.permission.map.class' => 'Symfony\\Component\\Security\\Acl\\Permission\\BasicPermissionMap',
            'security.acl.object_identity_retrieval_strategy.class' => 'Symfony\\Component\\Security\\Acl\\Domain\\ObjectIdentityRetrievalStrategy',
            'security.acl.security_identity_retrieval_strategy.class' => 'Symfony\\Component\\Security\\Acl\\Domain\\SecurityIdentityRetrievalStrategy',
            'security.acl.collection_cache.class' => 'Symfony\\Component\\Security\\Acl\\Domain\\AclCollectionCache',
            'security.acl.cache.doctrine.class' => 'Symfony\\Component\\Security\\Acl\\Domain\\DoctrineAclCache',
            'twig.class' => 'Twig_Environment',
            'twig.loader.filesystem.class' => 'Symfony\\Bundle\\TwigBundle\\Loader\\FilesystemLoader',
            'twig.loader.chain.class' => 'Twig_Loader_Chain',
            'templating.engine.twig.class' => 'Symfony\\Bundle\\TwigBundle\\TwigEngine',
            'twig.cache_warmer.class' => 'Symfony\\Bundle\\TwigBundle\\CacheWarmer\\TemplateCacheCacheWarmer',
            'twig.extension.trans.class' => 'Symfony\\Bridge\\Twig\\Extension\\TranslationExtension',
            'twig.extension.actions.class' => 'Symfony\\Bundle\\TwigBundle\\Extension\\ActionsExtension',
            'twig.extension.code.class' => 'Symfony\\Bridge\\Twig\\Extension\\CodeExtension',
            'twig.extension.routing.class' => 'Symfony\\Bridge\\Twig\\Extension\\RoutingExtension',
            'twig.extension.yaml.class' => 'Symfony\\Bridge\\Twig\\Extension\\YamlExtension',
            'twig.extension.form.class' => 'Symfony\\Bridge\\Twig\\Extension\\FormExtension',
            'twig.extension.httpkernel.class' => 'Symfony\\Bridge\\Twig\\Extension\\HttpKernelExtension',
            'twig.extension.debug.stopwatch.class' => 'Symfony\\Bridge\\Twig\\Extension\\StopwatchExtension',
            'twig.extension.expression.class' => 'Symfony\\Bridge\\Twig\\Extension\\ExpressionExtension',
            'twig.form.engine.class' => 'Symfony\\Bridge\\Twig\\Form\\TwigRendererEngine',
            'twig.form.renderer.class' => 'Symfony\\Bridge\\Twig\\Form\\TwigRenderer',
            'twig.translation.extractor.class' => 'Symfony\\Bridge\\Twig\\Translation\\TwigExtractor',
            'twig.exception_listener.class' => 'Symfony\\Component\\HttpKernel\\EventListener\\ExceptionListener',
            'twig.controller.exception.class' => 'Symfony\\Bundle\\TwigBundle\\Controller\\ExceptionController',
            'twig.controller.preview_error.class' => 'Symfony\\Bundle\\TwigBundle\\Controller\\PreviewErrorController',
            'twig.exception_listener.controller' => 'twig.controller.exception:showAction',
            'twig.form.resources' => array(
                0 => 'form_div_layout.html.twig',
                1 => 'LiipImagineBundle:Form:form_div_layout.html.twig',
            ),
            'monolog.logger.class' => 'Symfony\\Bridge\\Monolog\\Logger',
            'monolog.gelf.publisher.class' => 'Gelf\\MessagePublisher',
            'monolog.gelfphp.publisher.class' => 'Gelf\\Publisher',
            'monolog.handler.stream.class' => 'Monolog\\Handler\\StreamHandler',
            'monolog.handler.console.class' => 'Symfony\\Bridge\\Monolog\\Handler\\ConsoleHandler',
            'monolog.handler.group.class' => 'Monolog\\Handler\\GroupHandler',
            'monolog.handler.buffer.class' => 'Monolog\\Handler\\BufferHandler',
            'monolog.handler.rotating_file.class' => 'Monolog\\Handler\\RotatingFileHandler',
            'monolog.handler.syslog.class' => 'Monolog\\Handler\\SyslogHandler',
            'monolog.handler.syslogudp.class' => 'Monolog\\Handler\\SyslogUdpHandler',
            'monolog.handler.null.class' => 'Monolog\\Handler\\NullHandler',
            'monolog.handler.test.class' => 'Monolog\\Handler\\TestHandler',
            'monolog.handler.gelf.class' => 'Monolog\\Handler\\GelfHandler',
            'monolog.handler.rollbar.class' => 'Monolog\\Handler\\RollbarHandler',
            'monolog.handler.flowdock.class' => 'Monolog\\Handler\\FlowdockHandler',
            'monolog.handler.browser_console.class' => 'Monolog\\Handler\\BrowserConsoleHandler',
            'monolog.handler.firephp.class' => 'Symfony\\Bridge\\Monolog\\Handler\\FirePHPHandler',
            'monolog.handler.chromephp.class' => 'Symfony\\Bridge\\Monolog\\Handler\\ChromePhpHandler',
            'monolog.handler.debug.class' => 'Symfony\\Bridge\\Monolog\\Handler\\DebugHandler',
            'monolog.handler.swift_mailer.class' => 'Symfony\\Bridge\\Monolog\\Handler\\SwiftMailerHandler',
            'monolog.handler.native_mailer.class' => 'Monolog\\Handler\\NativeMailerHandler',
            'monolog.handler.socket.class' => 'Monolog\\Handler\\SocketHandler',
            'monolog.handler.pushover.class' => 'Monolog\\Handler\\PushoverHandler',
            'monolog.handler.raven.class' => 'Monolog\\Handler\\RavenHandler',
            'monolog.handler.newrelic.class' => 'Monolog\\Handler\\NewRelicHandler',
            'monolog.handler.hipchat.class' => 'Monolog\\Handler\\HipChatHandler',
            'monolog.handler.slack.class' => 'Monolog\\Handler\\SlackHandler',
            'monolog.handler.cube.class' => 'Monolog\\Handler\\CubeHandler',
            'monolog.handler.amqp.class' => 'Monolog\\Handler\\AmqpHandler',
            'monolog.handler.error_log.class' => 'Monolog\\Handler\\ErrorLogHandler',
            'monolog.handler.loggly.class' => 'Monolog\\Handler\\LogglyHandler',
            'monolog.handler.logentries.class' => 'Monolog\\Handler\\LogEntriesHandler',
            'monolog.handler.whatfailuregroup.class' => 'Monolog\\Handler\\WhatFailureGroupHandler',
            'monolog.activation_strategy.not_found.class' => 'Symfony\\Bundle\\MonologBundle\\NotFoundActivationStrategy',
            'monolog.handler.fingers_crossed.class' => 'Monolog\\Handler\\FingersCrossedHandler',
            'monolog.handler.fingers_crossed.error_level_activation_strategy.class' => 'Monolog\\Handler\\FingersCrossed\\ErrorLevelActivationStrategy',
            'monolog.handler.filter.class' => 'Monolog\\Handler\\FilterHandler',
            'monolog.handler.mongo.class' => 'Monolog\\Handler\\MongoDBHandler',
            'monolog.mongo.client.class' => 'MongoClient',
            'monolog.handler.elasticsearch.class' => 'Monolog\\Handler\\ElasticSearchHandler',
            'monolog.elastica.client.class' => 'Elastica\\Client',
            'monolog.swift_mailer.handlers' => array(

            ),
            'monolog.handlers_to_channels' => array(
                'monolog.handler.console' => NULL,
                'monolog.handler.main' => NULL,
            ),
            'swiftmailer.class' => 'Swift_Mailer',
            'swiftmailer.transport.sendmail.class' => 'Swift_Transport_SendmailTransport',
            'swiftmailer.transport.mail.class' => 'Swift_Transport_MailTransport',
            'swiftmailer.transport.failover.class' => 'Swift_Transport_FailoverTransport',
            'swiftmailer.plugin.redirecting.class' => 'Swift_Plugins_RedirectingPlugin',
            'swiftmailer.plugin.impersonate.class' => 'Swift_Plugins_ImpersonatePlugin',
            'swiftmailer.plugin.messagelogger.class' => 'Swift_Plugins_MessageLogger',
            'swiftmailer.plugin.antiflood.class' => 'Swift_Plugins_AntiFloodPlugin',
            'swiftmailer.transport.smtp.class' => 'Swift_Transport_EsmtpTransport',
            'swiftmailer.plugin.blackhole.class' => 'Swift_Plugins_BlackholePlugin',
            'swiftmailer.spool.file.class' => 'Swift_FileSpool',
            'swiftmailer.spool.memory.class' => 'Swift_MemorySpool',
            'swiftmailer.email_sender.listener.class' => 'Symfony\\Bundle\\SwiftmailerBundle\\EventListener\\EmailSenderListener',
            'swiftmailer.data_collector.class' => 'Symfony\\Bundle\\SwiftmailerBundle\\DataCollector\\MessageDataCollector',
            'swiftmailer.mailer.default.transport.name' => 'smtp',
            'swiftmailer.mailer.default.delivery.enabled' => true,
            'swiftmailer.mailer.default.transport.smtp.encryption' => 'ssl',
            'swiftmailer.mailer.default.transport.smtp.port' => 465,
            'swiftmailer.mailer.default.transport.smtp.host' => 'smtp.yandex.ru',
            'swiftmailer.mailer.default.transport.smtp.username' => 'robot@maxi-booking.ru',
            'swiftmailer.mailer.default.transport.smtp.password' => 'ghjlflbv10rjgbq',
            'swiftmailer.mailer.default.transport.smtp.auth_mode' => NULL,
            'swiftmailer.mailer.default.transport.smtp.timeout' => 30,
            'swiftmailer.mailer.default.transport.smtp.source_ip' => NULL,
            'swiftmailer.spool.default.memory.path' => (__DIR__.'/swiftmailer/spool/default'),
            'swiftmailer.mailer.default.spool.enabled' => true,
            'swiftmailer.mailer.default.plugin.impersonate' => NULL,
            'swiftmailer.mailer.default.single_address' => NULL,
            'swiftmailer.spool.enabled' => true,
            'swiftmailer.delivery.enabled' => true,
            'swiftmailer.single_address' => NULL,
            'swiftmailer.mailers' => array(
                'default' => 'swiftmailer.mailer.default',
            ),
            'swiftmailer.default_mailer' => 'default',
            'assetic.asset_factory.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\AssetFactory',
            'assetic.asset_manager.class' => 'Assetic\\Factory\\LazyAssetManager',
            'assetic.asset_manager_cache_warmer.class' => 'Symfony\\Bundle\\AsseticBundle\\CacheWarmer\\AssetManagerCacheWarmer',
            'assetic.cached_formula_loader.class' => 'Assetic\\Factory\\Loader\\CachedFormulaLoader',
            'assetic.config_cache.class' => 'Assetic\\Cache\\ConfigCache',
            'assetic.config_loader.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Loader\\ConfigurationLoader',
            'assetic.config_resource.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Resource\\ConfigurationResource',
            'assetic.coalescing_directory_resource.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Resource\\CoalescingDirectoryResource',
            'assetic.directory_resource.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Resource\\DirectoryResource',
            'assetic.filter_manager.class' => 'Symfony\\Bundle\\AsseticBundle\\FilterManager',
            'assetic.worker.ensure_filter.class' => 'Assetic\\Factory\\Worker\\EnsureFilterWorker',
            'assetic.worker.cache_busting.class' => 'Assetic\\Factory\\Worker\\CacheBustingWorker',
            'assetic.value_supplier.class' => 'Symfony\\Bundle\\AsseticBundle\\DefaultValueSupplier',
            'assetic.node.paths' => array(

            ),
            'assetic.cache_dir' => (__DIR__.'/assetic'),
            'assetic.bundles' => array(
                0 => 'FrameworkBundle',
                1 => 'SecurityBundle',
                2 => 'TwigBundle',
                3 => 'MonologBundle',
                4 => 'SwiftmailerBundle',
                5 => 'AsseticBundle',
                6 => 'SensioFrameworkExtraBundle',
                7 => 'DoctrineMongoDBBundle',
                8 => 'StofDoctrineExtensionsBundle',
                9 => 'FOSUserBundle',
                10 => 'FOSJsRoutingBundle',
                11 => 'KnpMenuBundle',
                12 => 'ObHighchartsBundle',
                13 => 'KnpSnappyBundle',
                14 => 'MisdGuzzleBundle',
                15 => 'IamPersistentMongoDBAclBundle',
                16 => 'LiipImagineBundle',
                17 => 'JMSDiExtraBundle',
                18 => 'JMSAopBundle',
                19 => 'JMSTranslationBundle',
                20 => 'LiuggioExcelBundle',
                21 => 'OrnicarGravatarBundle',
                22 => 'DoctrineFixturesBundle',
                23 => 'LswMemcacheBundle',
                24 => 'MBHBaseBundle',
                25 => 'MBHUserBundle',
                26 => 'MBHHotelBundle',
                27 => 'MBHPriceBundle',
                28 => 'MBHPackageBundle',
                29 => 'MBHCashBundle',
                30 => 'MBHChannelManagerBundle',
                31 => 'MBHOnlineBundle',
                32 => 'MBHDemoBundle',
                33 => 'MBHClientBundle',
                34 => 'MBHVegaBundle',
                35 => 'MBHWarehouseBundle',
                36 => 'MBHRestaurantBundle',
                37 => 'WebProfilerBundle',
                38 => 'SensioDistributionBundle',
                39 => 'SensioGeneratorBundle',
                40 => 'DebugBundle',
            ),
            'assetic.twig_extension.class' => 'Symfony\\Bundle\\AsseticBundle\\Twig\\AsseticExtension',
            'assetic.twig_formula_loader.class' => 'Assetic\\Extension\\Twig\\TwigFormulaLoader',
            'assetic.helper.dynamic.class' => 'Symfony\\Bundle\\AsseticBundle\\Templating\\DynamicAsseticHelper',
            'assetic.helper.static.class' => 'Symfony\\Bundle\\AsseticBundle\\Templating\\StaticAsseticHelper',
            'assetic.php_formula_loader.class' => 'Symfony\\Bundle\\AsseticBundle\\Factory\\Loader\\AsseticHelperFormulaLoader',
            'assetic.debug' => true,
            'assetic.use_controller' => false,
            'assetic.enable_profiler' => false,
            'assetic.read_from' => ($this->targetDirs[2].'/../web'),
            'assetic.write_to' => ($this->targetDirs[2].'/../web'),
            'assetic.variables' => array(

            ),
            'assetic.java.bin' => '/usr/bin/java',
            'assetic.node.bin' => '/usr/bin/node',
            'assetic.ruby.bin' => '/usr/bin/ruby',
            'assetic.sass.bin' => '/usr/bin/sass',
            'assetic.filter.cssrewrite.class' => 'Assetic\\Filter\\CssRewriteFilter',
            'assetic.filter.uglifycss.class' => 'Assetic\\Filter\\UglifyCssFilter',
            'assetic.filter.uglifycss.bin' => '/usr/local/bin/uglifycss',
            'assetic.filter.uglifycss.node' => '/usr/bin/nodejs',
            'assetic.filter.uglifycss.timeout' => NULL,
            'assetic.filter.uglifycss.node_paths' => array(

            ),
            'assetic.filter.uglifycss.expand_vars' => false,
            'assetic.filter.uglifycss.ugly_comments' => false,
            'assetic.filter.uglifycss.cute_comments' => false,
            'assetic.filter.uglifyjs2.class' => 'Assetic\\Filter\\UglifyJs2Filter',
            'assetic.filter.uglifyjs2.bin' => '/usr/local/bin/uglifyjs',
            'assetic.filter.uglifyjs2.node' => '/usr/bin/nodejs',
            'assetic.filter.uglifyjs2.timeout' => NULL,
            'assetic.filter.uglifyjs2.node_paths' => array(

            ),
            'assetic.filter.uglifyjs2.compress' => false,
            'assetic.filter.uglifyjs2.beautify' => false,
            'assetic.filter.uglifyjs2.mangle' => false,
            'assetic.filter.uglifyjs2.screw_ie8' => false,
            'assetic.filter.uglifyjs2.comments' => false,
            'assetic.filter.uglifyjs2.wrap' => false,
            'assetic.filter.uglifyjs2.defines' => array(

            ),
            'assetic.filter.less.class' => 'Assetic\\Filter\\LessFilter',
            'assetic.filter.less.node' => '/usr/bin/nodejs',
            'assetic.filter.less.node_paths' => array(
                0 => '/usr/local/lib/node_modules/',
            ),
            'assetic.filter.less.timeout' => NULL,
            'assetic.filter.less.compress' => NULL,
            'assetic.filter.less.load_paths' => array(

            ),
            'assetic.filter.scssphp.class' => 'Assetic\\Filter\\ScssphpFilter',
            'assetic.filter.scssphp.import_paths' => array(

            ),
            'assetic.filter.scssphp.compass' => false,
            'assetic.filter.scssphp.variables' => array(

            ),
            'assetic.filter.scssphp.formatter' => 'Leafo\\ScssPhp\\Formatter\\Compressed',
            'assetic.twig_extension.functions' => array(

            ),
            'sensio_framework_extra.view.guesser.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Templating\\TemplateGuesser',
            'sensio_framework_extra.controller.listener.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ControllerListener',
            'sensio_framework_extra.routing.loader.annot_dir.class' => 'Symfony\\Component\\Routing\\Loader\\AnnotationDirectoryLoader',
            'sensio_framework_extra.routing.loader.annot_file.class' => 'Symfony\\Component\\Routing\\Loader\\AnnotationFileLoader',
            'sensio_framework_extra.routing.loader.annot_class.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Routing\\AnnotatedRouteControllerLoader',
            'sensio_framework_extra.converter.listener.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\ParamConverterListener',
            'sensio_framework_extra.converter.manager.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\ParamConverterManager',
            'sensio_framework_extra.converter.doctrine.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DoctrineParamConverter',
            'sensio_framework_extra.converter.datetime.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\Request\\ParamConverter\\DateTimeParamConverter',
            'sensio_framework_extra.view.listener.class' => 'Sensio\\Bundle\\FrameworkExtraBundle\\EventListener\\TemplateListener',
            'doctrine_mongodb.odm.connection.class' => 'Doctrine\\MongoDB\\Connection',
            'doctrine_mongodb.odm.configuration.class' => 'Doctrine\\ODM\\MongoDB\\Configuration',
            'doctrine_mongodb.odm.document_manager.class' => 'Doctrine\\ODM\\MongoDB\\DocumentManager',
            'doctrine_mongodb.odm.manager_configurator.class' => 'Doctrine\\Bundle\\MongoDBBundle\\ManagerConfigurator',
            'doctrine_mongodb.odm.logger.class' => 'Doctrine\\Bundle\\MongoDBBundle\\Logger\\Logger',
            'doctrine_mongodb.odm.logger.aggregate.class' => 'Doctrine\\Bundle\\MongoDBBundle\\Logger\\AggregateLogger',
            'doctrine_mongodb.odm.data_collector.standard.class' => 'Doctrine\\Bundle\\MongoDBBundle\\DataCollector\\StandardDataCollector',
            'doctrine_mongodb.odm.data_collector.pretty.class' => 'Doctrine\\Bundle\\MongoDBBundle\\DataCollector\\PrettyDataCollector',
            'doctrine_mongodb.odm.event_manager.class' => 'Symfony\\Bridge\\Doctrine\\ContainerAwareEventManager',
            'doctrine_odm.mongodb.validator_initializer.class' => 'Symfony\\Bridge\\Doctrine\\Validator\\DoctrineInitializer',
            'doctrine_odm.mongodb.validator.unique.class' => 'Symfony\\Bridge\\Doctrine\\Validator\\Constraints\\UniqueEntityValidator',
            'doctrine_mongodb.odm.class' => 'Doctrine\\Bundle\\MongoDBBundle\\ManagerRegistry',
            'doctrine_mongodb.odm.security.user.provider.class' => 'IamPersistent\\MongoDBAclBundle\\Security\\DocumentUserProvider',
            'doctrine_mongodb.odm.proxy_cache_warmer.class' => 'Doctrine\\Bundle\\MongoDBBundle\\CacheWarmer\\ProxyCacheWarmer',
            'doctrine_mongodb.odm.hydrator_cache_warmer.class' => 'Doctrine\\Bundle\\MongoDBBundle\\CacheWarmer\\HydratorCacheWarmer',
            'doctrine_mongodb.odm.cache.array.class' => 'Doctrine\\Common\\Cache\\ArrayCache',
            'doctrine_mongodb.odm.cache.apc.class' => 'Doctrine\\Common\\Cache\\ApcCache',
            'doctrine_mongodb.odm.cache.memcache.class' => 'Doctrine\\Common\\Cache\\MemcacheCache',
            'doctrine_mongodb.odm.cache.memcache_host' => 'localhost',
            'doctrine_mongodb.odm.cache.memcache_port' => 11211,
            'doctrine_mongodb.odm.cache.memcache_instance.class' => 'Memcache',
            'doctrine_mongodb.odm.cache.xcache.class' => 'Doctrine\\Common\\Cache\\XcacheCache',
            'doctrine_mongodb.odm.metadata.driver_chain.class' => 'Doctrine\\Common\\Persistence\\Mapping\\Driver\\MappingDriverChain',
            'doctrine_mongodb.odm.metadata.annotation.class' => 'Doctrine\\ODM\\MongoDB\\Mapping\\Driver\\AnnotationDriver',
            'doctrine_mongodb.odm.metadata.xml.class' => 'Doctrine\\Bundle\\MongoDBBundle\\Mapping\\Driver\\XmlDriver',
            'doctrine_mongodb.odm.metadata.yml.class' => 'Doctrine\\Bundle\\MongoDBBundle\\Mapping\\Driver\\YamlDriver',
            'doctrine_mongodb.odm.mapping_dirs' => array(

            ),
            'doctrine_mongodb.odm.xml_mapping_dirs' => array(

            ),
            'doctrine_mongodb.odm.yml_mapping_dirs' => array(

            ),
            'doctrine_mongodb.odm.document_dirs' => array(

            ),
            'doctrine_mongodb.odm.fixtures_dirs' => array(

            ),
            'doctrine_mongodb.odm.logger.batch_insert_threshold' => 4,
            'doctrine_mongodb.odm.listeners.resolve_target_document.class' => 'Doctrine\\ODM\\MongoDB\\Tools\\ResolveTargetDocumentListener',
            'doctrine_mongodb.odm.default_connection' => 'default',
            'doctrine_mongodb.odm.default_document_manager' => 'default',
            'doctrine_mongodb.odm.proxy_namespace' => 'MongoDBODMProxies',
            'doctrine_mongodb.odm.proxy_dir' => (__DIR__.'/doctrine/odm/mongodb/Proxies'),
            'doctrine_mongodb.odm.auto_generate_proxy_classes' => 0,
            'doctrine_mongodb.odm.hydrator_namespace' => 'Hydrators',
            'doctrine_mongodb.odm.hydrator_dir' => (__DIR__.'/doctrine/odm/mongodb/Hydrators'),
            'doctrine_mongodb.odm.auto_generate_hydrator_classes' => 0,
            'doctrine_mongodb.odm.default_commit_options' => array(

            ),
            'doctrine_mongodb.odm.fixture_loader' => 'Symfony\\Bridge\\Doctrine\\DataFixtures\\ContainerAwareLoader',
            'doctrine_mongodb.odm.connections' => array(
                'default' => 'doctrine_mongodb.odm.default_connection',
            ),
            'doctrine_mongodb.odm.document_managers' => array(
                'default' => 'doctrine_mongodb.odm.default_document_manager',
            ),
            'stof_doctrine_extensions.event_listener.locale.class' => 'Stof\\DoctrineExtensionsBundle\\EventListener\\LocaleListener',
            'stof_doctrine_extensions.event_listener.logger.class' => 'Stof\\DoctrineExtensionsBundle\\EventListener\\LoggerListener',
            'stof_doctrine_extensions.event_listener.blame.class' => 'Stof\\DoctrineExtensionsBundle\\EventListener\\BlameListener',
            'stof_doctrine_extensions.uploadable.manager.class' => 'Stof\\DoctrineExtensionsBundle\\Uploadable\\UploadableManager',
            'stof_doctrine_extensions.uploadable.mime_type_guesser.class' => 'Stof\\DoctrineExtensionsBundle\\Uploadable\\MimeTypeGuesserAdapter',
            'stof_doctrine_extensions.uploadable.default_file_info.class' => 'Stof\\DoctrineExtensionsBundle\\Uploadable\\UploadedFileInfo',
            'stof_doctrine_extensions.default_locale' => 'ru_RU',
            'stof_doctrine_extensions.default_file_path' => NULL,
            'stof_doctrine_extensions.translation_fallback' => true,
            'stof_doctrine_extensions.persist_default_translation' => false,
            'stof_doctrine_extensions.skip_translation_on_load' => false,
            'stof_doctrine_extensions.uploadable.validate_writable_directory' => true,
            'stof_doctrine_extensions.listener.translatable.class' => 'Gedmo\\Translatable\\TranslatableListener',
            'stof_doctrine_extensions.listener.timestampable.class' => 'Gedmo\\Timestampable\\TimestampableListener',
            'stof_doctrine_extensions.listener.blameable.class' => 'Gedmo\\Blameable\\BlameableListener',
            'stof_doctrine_extensions.listener.sluggable.class' => 'Gedmo\\Sluggable\\SluggableListener',
            'stof_doctrine_extensions.listener.tree.class' => 'Gedmo\\Tree\\TreeListener',
            'stof_doctrine_extensions.listener.loggable.class' => 'Gedmo\\Loggable\\LoggableListener',
            'stof_doctrine_extensions.listener.sortable.class' => 'Gedmo\\Sortable\\SortableListener',
            'stof_doctrine_extensions.listener.softdeleteable.class' => 'Gedmo\\SoftDeleteable\\SoftDeleteableListener',
            'stof_doctrine_extensions.listener.uploadable.class' => 'Gedmo\\Uploadable\\UploadableListener',
            'stof_doctrine_extensions.listener.reference_integrity.class' => 'Gedmo\\ReferenceIntegrity\\ReferenceIntegrityListener',
            'fos_user.backend_type_mongodb' => true,
            'fos_user.security.interactive_login_listener.class' => 'FOS\\UserBundle\\EventListener\\LastLoginListener',
            'fos_user.security.login_manager.class' => 'FOS\\UserBundle\\Security\\LoginManager',
            'fos_user.resetting.email.template' => 'FOSUserBundle:Resetting:email.txt.twig',
            'fos_user.registration.confirmation.template' => 'FOSUserBundle:Registration:email.txt.twig',
            'fos_user.storage' => 'mongodb',
            'fos_user.firewall_name' => 'main',
            'fos_user.model_manager_name' => NULL,
            'fos_user.model.user.class' => 'MBH\\Bundle\\UserBundle\\Document\\User',
            'fos_user.profile.form.type' => 'fos_user_profile',
            'fos_user.profile.form.name' => 'fos_user_profile_form',
            'fos_user.profile.form.validation_groups' => array(
                0 => 'Profile',
                1 => 'Default',
            ),
            'fos_user.registration.confirmation.from_email' => array(
                'webmaster@example.com' => 'webmaster',
            ),
            'fos_user.registration.confirmation.enabled' => false,
            'fos_user.registration.form.type' => 'fos_user_registration',
            'fos_user.registration.form.name' => 'fos_user_registration_form',
            'fos_user.registration.form.validation_groups' => array(
                0 => 'Registration',
                1 => 'Default',
            ),
            'fos_user.change_password.form.type' => 'fos_user_change_password',
            'fos_user.change_password.form.name' => 'fos_user_change_password_form',
            'fos_user.change_password.form.validation_groups' => array(
                0 => 'ChangePassword',
                1 => 'Default',
            ),
            'fos_user.resetting.email.from_email' => array(
                'webmaster@example.com' => 'webmaster',
            ),
            'fos_user.resetting.token_ttl' => 86400,
            'fos_user.resetting.form.type' => 'fos_user_resetting',
            'fos_user.resetting.form.name' => 'fos_user_resetting_form',
            'fos_user.resetting.form.validation_groups' => array(
                0 => 'ResetPassword',
                1 => 'Default',
            ),
            'fos_user.group_manager.class' => 'FOS\\UserBundle\\Doctrine\\GroupManager',
            'fos_user.model.group.class' => 'MBH\\Bundle\\UserBundle\\Document\\Group',
            'fos_user.group.form.type' => 'fos_user_group',
            'fos_user.group.form.name' => 'fos_user_group_form',
            'fos_user.group.form.validation_groups' => array(
                0 => 'Registration',
                1 => 'Default',
            ),
            'fos_js_routing.extractor.class' => 'FOS\\JsRoutingBundle\\Extractor\\ExposedRoutesExtractor',
            'fos_js_routing.controller.class' => 'FOS\\JsRoutingBundle\\Controller\\Controller',
            'fos_js_routing.cache_control' => array(
                'enabled' => false,
            ),
            'knp_menu.factory.class' => 'Knp\\Menu\\MenuFactory',
            'knp_menu.factory_extension.routing.class' => 'Knp\\Menu\\Integration\\Symfony\\RoutingExtension',
            'knp_menu.helper.class' => 'Knp\\Menu\\Twig\\Helper',
            'knp_menu.matcher.class' => 'Knp\\Menu\\Matcher\\Matcher',
            'knp_menu.menu_provider.chain.class' => 'Knp\\Menu\\Provider\\ChainProvider',
            'knp_menu.menu_provider.container_aware.class' => 'Knp\\Bundle\\MenuBundle\\Provider\\ContainerAwareProvider',
            'knp_menu.menu_provider.builder_alias.class' => 'Knp\\Bundle\\MenuBundle\\Provider\\BuilderAliasProvider',
            'knp_menu.renderer_provider.class' => 'Knp\\Bundle\\MenuBundle\\Renderer\\ContainerAwareProvider',
            'knp_menu.renderer.list.class' => 'Knp\\Menu\\Renderer\\ListRenderer',
            'knp_menu.renderer.list.options' => array(

            ),
            'knp_menu.listener.voters.class' => 'Knp\\Bundle\\MenuBundle\\EventListener\\VoterInitializerListener',
            'knp_menu.voter.router.class' => 'Knp\\Menu\\Matcher\\Voter\\RouteVoter',
            'knp_menu.twig.extension.class' => 'Knp\\Menu\\Twig\\MenuExtension',
            'knp_menu.renderer.twig.class' => 'Knp\\Menu\\Renderer\\TwigRenderer',
            'knp_menu.renderer.twig.template' => 'MBHBaseBundle:Menu:menu.html.twig',
            'knp_menu.default_renderer' => 'twig',
            'ob_highcharts.twig_extension.class' => 'Ob\\HighchartsBundle\\Twig\\HighchartsExtension',
            'knp_snappy.pdf.internal_generator.class' => 'Knp\\Snappy\\Pdf',
            'knp_snappy.pdf.class' => 'Knp\\Bundle\\SnappyBundle\\Snappy\\LoggableGenerator',
            'knp_snappy.pdf.binary' => '/usr/local/bin/wkhtmltopdf',
            'knp_snappy.pdf.options' => array(

            ),
            'knp_snappy.pdf.env' => array(

            ),
            'knp_snappy.image.internal_generator.class' => 'Knp\\Snappy\\Image',
            'knp_snappy.image.class' => 'Knp\\Bundle\\SnappyBundle\\Snappy\\LoggableGenerator',
            'knp_snappy.image.binary' => '/usr/local/bin/h',
            'knp_snappy.image.options' => array(

            ),
            'knp_snappy.image.env' => array(

            ),
            'guzzle.client.class' => 'Guzzle\\Service\\Client',
            'misd_guzzle.listener.request_listener.class' => 'Misd\\GuzzleBundle\\EventListener\\RequestListener',
            'misd_guzzle.data_collector.class' => 'Misd\\GuzzleBundle\\DataCollector\\GuzzleDataCollector',
            'misd_guzzle.log.format' => '{hostname} {req_header_User-Agent} - [{ts}] "{method} {resource} {protocol}/{version}" {code} {res_header_Content-Length}',
            'guzzle.plugin.async.class' => 'Guzzle\\Plugin\\Async\\AsyncPlugin',
            'guzzle.plugin.backoff.class' => 'Guzzle\\Plugin\\Backoff\\BackoffPlugin',
            'guzzle.plugin.cache.class' => 'Guzzle\\Plugin\\Cache\\CachePlugin',
            'guzzle.plugin.cookie.class' => 'Guzzle\\Plugin\\Cookie\\CookiePlugin',
            'guzzle.plugin.curl_auth.class' => 'Guzzle\\Plugin\\CurlAuth\\CurlAuthPlugin',
            'guzzle.plugin.error_response.class' => 'Guzzle\\Plugin\\ErrorResponse\\ErrorResponsePlugin',
            'guzzle.plugin.history.class' => 'Guzzle\\Plugin\\History\\HistoryPlugin',
            'guzzle.plugin.log.class' => 'Guzzle\\Plugin\\Log\\LogPlugin',
            'guzzle.plugin.md5_validator.class' => 'Guzzle\\Plugin\\Md5\\Md5ValidatorPlugin',
            'guzzle.plugin.command_content_md5.class' => 'Guzzle\\Plugin\\Md5\\CommandContentMd5Plugin',
            'guzzle.plugin.mock.class' => 'Guzzle\\Plugin\\Mock\\MockPlugin',
            'guzzle.plugin.oauth.class' => 'Guzzle\\Plugin\\Oauth\\OauthPlugin',
            'guzzle.log.adapter.monolog.class' => 'Guzzle\\Log\\MonologLogAdapter',
            'guzzle.log.adapter.array.class' => 'Guzzle\\Log\\ArrayLogAdapter',
            'misd_guzzle.listener.command.class' => 'Misd\\GuzzleBundle\\EventListener\\CommandListener',
            'misd_guzzle.request.visitor.body.class' => 'Misd\\GuzzleBundle\\Service\\Command\\LocationVisitor\\Request\\JMSSerializerBodyVisitor',
            'misd_guzzle.response.parser.class' => 'Misd\\GuzzleBundle\\Service\\Command\\JMSSerializerResponseParser',
            'misd_guzzle.response.parser.fallback.class' => 'Guzzle\\Service\\Command\\OperationResponseParser',
            'misd_guzzle.param_converter.class' => 'Misd\\GuzzleBundle\\Request\\ParamConverter\\GuzzleParamConverter3x',
            'guzzle.service_description.class' => 'Guzzle\\Service\\Description\\ServiceDescription',
            'guzzle.service_builder.class' => 'Guzzle\\Service\\Builder\\ServiceBuilder',
            'guzzle.service_builder.configuration_file' => ($this->targetDirs[2].'/config/webservices.json'),
            'guzzle.cache.doctrine.class' => 'Guzzle\\Cache\\DoctrineCacheAdapter',
            'guzzle.cache.doctrine.filesystem.class' => 'Doctrine\\Common\\Cache\\FilesystemCache',
            'misd_guzzle.cache.filesystem.path' => (__DIR__.'/guzzle/'),
            'misd_guzzle.log.enabled' => true,
            'doctrine_mongodb.odm.security.acl.provider.class' => 'IamPersistent\\MongoDBAclBundle\\Security\\Acl\\MutableAclProvider',
            'doctrine_mongodb.odm.security.acl.database' => 'mbh',
            'doctrine_mongodb.odm.security.acl.entry_collection' => 'acl_entry',
            'doctrine_mongodb.odm.security.acl.oid_collection' => 'acl_oid',
            'liip_imagine.filter.configuration.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\FilterConfiguration',
            'liip_imagine.filter.manager.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\FilterManager',
            'liip_imagine.data.manager.class' => 'Liip\\ImagineBundle\\Imagine\\Data\\DataManager',
            'liip_imagine.cache.manager.class' => 'Liip\\ImagineBundle\\Imagine\\Cache\\CacheManager',
            'liip_imagine.cache.signer.class' => 'Liip\\ImagineBundle\\Imagine\\Cache\\Signer',
            'liip_imagine.binary.mime_type_guesser.class' => 'Liip\\ImagineBundle\\Binary\\SimpleMimeTypeGuesser',
            'liip_imagine.controller.class' => 'Liip\\ImagineBundle\\Controller\\ImagineController',
            'liip_imagine.twig.extension.class' => 'Liip\\ImagineBundle\\Templating\\ImagineExtension',
            'liip_imagine.templating.helper.class' => 'Liip\\ImagineBundle\\Templating\\Helper\\ImagineHelper',
            'liip_imagine.gd.class' => 'Imagine\\Gd\\Imagine',
            'liip_imagine.imagick.class' => 'Imagine\\Imagick\\Imagine',
            'liip_imagine.gmagick.class' => 'Imagine\\Gmagick\\Imagine',
            'liip_imagine.filter.loader.relative_resize.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\RelativeResizeFilterLoader',
            'liip_imagine.filter.loader.resize.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\ResizeFilterLoader',
            'liip_imagine.filter.loader.thumbnail.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\ThumbnailFilterLoader',
            'liip_imagine.filter.loader.crop.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\CropFilterLoader',
            'liip_imagine.filter.loader.paste.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\PasteFilterLoader',
            'liip_imagine.filter.loader.watermark.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\WatermarkFilterLoader',
            'liip_imagine.filter.loader.strip.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\StripFilterLoader',
            'liip_imagine.filter.loader.background.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\BackgroundFilterLoader',
            'liip_imagine.filter.loader.upscale.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\UpscaleFilterLoader',
            'liip_imagine.filter.loader.auto_rotate.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\AutoRotateFilterLoader',
            'liip_imagine.filter.loader.rotate.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\RotateFilterLoader',
            'liip_imagine.filter.loader.interlace.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\Loader\\InterlaceFilterLoader',
            'liip_imagine.binary.loader.filesystem.class' => 'Liip\\ImagineBundle\\Binary\\Loader\\FileSystemLoader',
            'liip_imagine.binary.loader.stream.class' => 'Liip\\ImagineBundle\\Binary\\Loader\\StreamLoader',
            'liip_imagine.cache.resolver.web_path.class' => 'Liip\\ImagineBundle\\Imagine\\Cache\\Resolver\\WebPathResolver',
            'liip_imagine.cache.resolver.no_cache_web_path.class' => 'Liip\\ImagineBundle\\Imagine\\Cache\\Resolver\\NoCacheWebPathResolver',
            'liip_imagine.cache.resolver.aws_s3.class' => 'Liip\\ImagineBundle\\Imagine\\Cache\\Resolver\\AwsS3Resolver',
            'liip_imagine.cache.resolver.cache.class' => 'Liip\\ImagineBundle\\Imagine\\Cache\\Resolver\\CacheResolver',
            'liip_imagine.cache.resolver.proxy.class' => 'Liip\\ImagineBundle\\Imagine\\Cache\\Resolver\\ProxyResolver',
            'liip_imagine.form.type.image.class' => 'Liip\\ImagineBundle\\Form\\Type\\ImageType',
            'liip_imagine.meta_data.reader.class' => 'Imagine\\Image\\Metadata\\ExifMetadataReader',
            'liip_imagine.filter.post_processor.jpegoptim.class' => 'Liip\\ImagineBundle\\Imagine\\Filter\\PostProcessor\\JpegOptimPostProcessor',
            'liip_imagine.jpegoptim.binary' => '/usr/bin/jpegoptim',
            'liip_imagine.cache.resolver.default' => 'default',
            'liip_imagine.default_image' => NULL,
            'liip_imagine.filter_sets' => array(
                'cache' => array(
                    'quality' => 100,
                    'jpeg_quality' => NULL,
                    'png_compression_level' => NULL,
                    'png_compression_filter' => NULL,
                    'format' => NULL,
                    'animated' => false,
                    'cache' => NULL,
                    'data_loader' => NULL,
                    'default_image' => NULL,
                    'filters' => array(

                    ),
                    'post_processors' => array(

                    ),
                ),
                'thumb_100x100' => array(
                    'quality' => 100,
                    'filters' => array(
                        'thumbnail' => array(
                            'size' => array(
                                0 => 100,
                                1 => 100,
                            ),
                            'mode' => 'outbound',
                            'allow_upscale' => true,
                        ),
                    ),
                    'jpeg_quality' => NULL,
                    'png_compression_level' => NULL,
                    'png_compression_filter' => NULL,
                    'format' => NULL,
                    'animated' => false,
                    'cache' => NULL,
                    'data_loader' => NULL,
                    'default_image' => NULL,
                    'post_processors' => array(

                    ),
                ),
                'thumb_130x110' => array(
                    'quality' => 100,
                    'filters' => array(
                        'thumbnail' => array(
                            'size' => array(
                                0 => 130,
                                1 => 110,
                            ),
                            'mode' => 'outbound',
                            'allow_upscale' => true,
                        ),
                    ),
                    'jpeg_quality' => NULL,
                    'png_compression_level' => NULL,
                    'png_compression_filter' => NULL,
                    'format' => NULL,
                    'animated' => false,
                    'cache' => NULL,
                    'data_loader' => NULL,
                    'default_image' => NULL,
                    'post_processors' => array(

                    ),
                ),
                'thumb_95x80' => array(
                    'quality' => 100,
                    'filters' => array(
                        'thumbnail' => array(
                            'size' => array(
                                0 => 95,
                                1 => 80,
                            ),
                            'mode' => 'outbound',
                            'allow_upscale' => true,
                        ),
                    ),
                    'jpeg_quality' => NULL,
                    'png_compression_level' => NULL,
                    'png_compression_filter' => NULL,
                    'format' => NULL,
                    'animated' => false,
                    'cache' => NULL,
                    'data_loader' => NULL,
                    'default_image' => NULL,
                    'post_processors' => array(

                    ),
                ),
                'stamp' => array(
                    'data_loader' => 'protected',
                    'quality' => 100,
                    'filters' => array(
                        'thumbnail' => array(
                            'size' => array(
                                0 => 10,
                                1 => 10,
                            ),
                            'mode' => 'outbound',
                            'allow_upscale' => true,
                        ),
                    ),
                    'jpeg_quality' => NULL,
                    'png_compression_level' => NULL,
                    'png_compression_filter' => NULL,
                    'format' => NULL,
                    'animated' => false,
                    'cache' => NULL,
                    'default_image' => NULL,
                    'post_processors' => array(

                    ),
                ),
                'scaler' => array(
                    'quality' => 100,
                    'filters' => array(
                        'relative_resize' => array(
                            'scale' => 0.5,
                            'allow_upscale' => true,
                        ),
                    ),
                    'jpeg_quality' => NULL,
                    'png_compression_level' => NULL,
                    'png_compression_filter' => NULL,
                    'format' => NULL,
                    'animated' => false,
                    'cache' => NULL,
                    'data_loader' => NULL,
                    'default_image' => NULL,
                    'post_processors' => array(

                    ),
                ),
            ),
            'liip_imagine.binary.loader.default' => 'default',
            'liip_imagine.controller.filter_action' => 'liip_imagine.controller:filterAction',
            'liip_imagine.controller.filter_runtime_action' => 'liip_imagine.controller:filterRuntimeAction',
            'jms_di_extra.metadata.driver.annotation_driver.class' => 'JMS\\DiExtraBundle\\Metadata\\Driver\\AnnotationDriver',
            'jms_di_extra.metadata.driver.configured_controller_injections.class' => 'JMS\\DiExtraBundle\\Metadata\\Driver\\ConfiguredControllerInjectionsDriver',
            'jms_di_extra.metadata.driver.lazy_loading_driver.class' => 'Metadata\\Driver\\LazyLoadingDriver',
            'jms_di_extra.metadata.metadata_factory.class' => 'Metadata\\MetadataFactory',
            'jms_di_extra.metadata.cache.file_cache.class' => 'Metadata\\Cache\\FileCache',
            'jms_di_extra.metadata.converter.class' => 'JMS\\DiExtraBundle\\Metadata\\MetadataConverter',
            'jms_di_extra.controller_resolver.class' => 'JMS\\DiExtraBundle\\HttpKernel\\ControllerResolver',
            'jms_di_extra.controller_injectors_warmer.class' => 'JMS\\DiExtraBundle\\HttpKernel\\ControllerInjectorsWarmer',
            'jms_di_extra.all_bundles' => false,
            'jms_di_extra.bundles' => array(

            ),
            'jms_di_extra.directories' => array(

            ),
            'jms_di_extra.cache_dir' => (__DIR__.'/jms_diextra'),
            'jms_di_extra.disable_grep' => false,
            'jms_di_extra.doctrine_integration' => false,
            'jms_di_extra.cache_warmer.controller_file_blacklist' => array(

            ),
            'jms_aop.cache_dir' => (__DIR__.'/jms_aop'),
            'jms_aop.interceptor_loader.class' => 'JMS\\AopBundle\\Aop\\InterceptorLoader',
            'jms_translation.twig_extension.class' => 'JMS\\TranslationBundle\\Twig\\TranslationExtension',
            'jms_translation.extractor_manager.class' => 'JMS\\TranslationBundle\\Translation\\ExtractorManager',
            'jms_translation.extractor.file_extractor.class' => 'JMS\\TranslationBundle\\Translation\\Extractor\\FileExtractor',
            'jms_translation.extractor.file.default_php_extractor' => 'JMS\\TranslationBundle\\Translation\\Extractor\\File\\DefaultPhpFileExtractor',
            'jms_translation.extractor.file.translation_container_extractor' => 'JMS\\TranslationBundle\\Translation\\Extractor\\File\\TranslationContainerExtractor',
            'jms_translation.extractor.file.twig_extractor' => 'JMS\\TranslationBundle\\Translation\\Extractor\\File\\TwigFileExtractor',
            'jms_translation.extractor.file.form_extractor.class' => 'JMS\\TranslationBundle\\Translation\\Extractor\\File\\FormExtractor',
            'jms_translation.extractor.file.validation_extractor.class' => 'JMS\\TranslationBundle\\Translation\\Extractor\\File\\ValidationExtractor',
            'jms_translation.extractor.file.authentication_message_extractor.class' => 'JMS\\TranslationBundle\\Translation\\Extractor\\File\\AuthenticationMessagesExtractor',
            'jms_translation.loader.symfony.xliff_loader.class' => 'JMS\\TranslationBundle\\Translation\\Loader\\Symfony\\XliffLoader',
            'jms_translation.loader.xliff_loader.class' => 'JMS\\TranslationBundle\\Translation\\Loader\\XliffLoader',
            'jms_translation.loader.symfony_adapter.class' => 'JMS\\TranslationBundle\\Translation\\Loader\\SymfonyLoaderAdapter',
            'jms_translation.loader_manager.class' => 'JMS\\TranslationBundle\\Translation\\LoaderManager',
            'jms_translation.dumper.php_dumper.class' => 'JMS\\TranslationBundle\\Translation\\Dumper\\PhpDumper',
            'jms_translation.dumper.xliff_dumper.class' => 'JMS\\TranslationBundle\\Translation\\Dumper\\XliffDumper',
            'jms_translation.dumper.yaml_dumper.class' => 'JMS\\TranslationBundle\\Translation\\Dumper\\YamlDumper',
            'jms_translation.dumper.symfony_adapter.class' => 'JMS\\TranslationBundle\\Translation\\Dumper\\SymfonyDumperAdapter',
            'jms_translation.file_writer.class' => 'JMS\\TranslationBundle\\Translation\\FileWriter',
            'jms_translation.updater.class' => 'JMS\\TranslationBundle\\Translation\\Updater',
            'jms_translation.config_factory.class' => 'JMS\\TranslationBundle\\Translation\\ConfigFactory',
            'jms_translation.source_language' => 'en',
            'jms_translation.locales' => array(

            ),
            'phpexcel.class' => 'Liuggio\\ExcelBundle\\Factory',
            'templating.helper.gravatar.class' => 'Ornicar\\GravatarBundle\\Templating\\Helper\\GravatarHelper',
            'twig.extension.gravatar.class' => 'Ornicar\\GravatarBundle\\Twig\\GravatarExtension',
            'gravatar.api.class' => 'Ornicar\\GravatarBundle\\GravatarApi',
            'memcache.doctrine_cache.class' => 'Lsw\\MemcacheBundle\\Doctrine\\Cache\\MemcacheCache',
            'memcache.session_handler.class' => 'Lsw\\MemcacheBundle\\Session\\Storage\\LockingSessionHandler',
            'memcache.firewall_handler.class' => 'Lsw\\MemcacheBundle\\Firewall\\FirewallHandler',
            'memcache.data_collector.class' => 'Lsw\\MemcacheBundle\\DataCollector\\MemcacheDataCollector',
            'memcache.data_collector.template' => 'LswMemcacheBundle:Collector:memcache',
            'memcache.session_handler.auto_load' => true,
            'web_profiler.controller.profiler.class' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController',
            'web_profiler.controller.router.class' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\RouterController',
            'web_profiler.controller.exception.class' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ExceptionController',
            'twig.extension.webprofiler.class' => 'Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension',
            'web_profiler.debug_toolbar.position' => 'bottom',
            'web_profiler.debug_toolbar.class' => 'Symfony\\Bundle\\WebProfilerBundle\\EventListener\\WebDebugToolbarListener',
            'web_profiler.debug_toolbar.intercept_redirects' => false,
            'web_profiler.debug_toolbar.mode' => 2,
            'sensio_distribution.webconfigurator.class' => 'Sensio\\Bundle\\DistributionBundle\\Configurator\\Configurator',
            'sensio_distribution.webconfigurator.doctrine_step.class' => 'Sensio\\Bundle\\DistributionBundle\\Configurator\\Step\\DoctrineStep',
            'sensio_distribution.webconfigurator.secret_step.class' => 'Sensio\\Bundle\\DistributionBundle\\Configurator\\Step\\SecretStep',
            'sensio_distribution.security_checker.class' => 'SensioLabs\\Security\\SecurityChecker',
            'sensio_distribution.security_checker.command.class' => 'SensioLabs\\Security\\Command\\SecurityCheckerCommand',
            'data_collector.templates' => array(
                'data_collector.request' => array(
                    0 => 'request',
                    1 => '@WebProfiler/Collector/request.html.twig',
                ),
                'data_collector.time' => array(
                    0 => 'time',
                    1 => '@WebProfiler/Collector/time.html.twig',
                ),
                'data_collector.memory' => array(
                    0 => 'memory',
                    1 => '@WebProfiler/Collector/memory.html.twig',
                ),
                'data_collector.ajax' => array(
                    0 => 'ajax',
                    1 => '@WebProfiler/Collector/ajax.html.twig',
                ),
                'data_collector.form' => array(
                    0 => 'form',
                    1 => '@WebProfiler/Collector/form.html.twig',
                ),
                'data_collector.exception' => array(
                    0 => 'exception',
                    1 => '@WebProfiler/Collector/exception.html.twig',
                ),
                'data_collector.logger' => array(
                    0 => 'logger',
                    1 => '@WebProfiler/Collector/logger.html.twig',
                ),
                'data_collector.events' => array(
                    0 => 'events',
                    1 => '@WebProfiler/Collector/events.html.twig',
                ),
                'data_collector.router' => array(
                    0 => 'router',
                    1 => '@WebProfiler/Collector/router.html.twig',
                ),
                'data_collector.translation' => array(
                    0 => 'translation',
                    1 => '@WebProfiler/Collector/translation.html.twig',
                ),
                'data_collector.security' => array(
                    0 => 'security',
                    1 => '@Security/Collector/security.html.twig',
                ),
                'data_collector.twig' => array(
                    0 => 'twig',
                    1 => '@WebProfiler/Collector/twig.html.twig',
                ),
                'data_collector.dump' => array(
                    0 => 'dump',
                    1 => '@Debug/Profiler/dump.html.twig',
                ),
                'swiftmailer.data_collector' => array(
                    0 => 'swiftmailer',
                    1 => '@Swiftmailer/Collector/swiftmailer.html.twig',
                ),
                'doctrine_mongodb.odm.data_collector.pretty' => array(
                    0 => 'mongodb',
                    1 => 'DoctrineMongoDBBundle:Collector:mongodb',
                ),
                'misd_guzzle.data_collector' => array(
                    0 => 'guzzle',
                    1 => 'MisdGuzzleBundle:Collector:guzzle',
                ),
                'memcache.data_collector' => array(
                    0 => 'memcache',
                    1 => 'LswMemcacheBundle:Collector:memcache',
                ),
                'data_collector.config' => array(
                    0 => 'config',
                    1 => '@WebProfiler/Collector/config.html.twig',
                ),
            ),
            'console.command.ids' => array(
                0 => 'sensio_distribution.security_checker.command',
            ),
        );
    }
}
