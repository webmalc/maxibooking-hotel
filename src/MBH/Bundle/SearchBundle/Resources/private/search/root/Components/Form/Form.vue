<template>
    <div class="data-form">
        <div class="input">
            <i class="fa fa-calendar" title="" data-toggle="tooltip" data-original-title="Заезд"></i>&nbsp;
            <RangePicker/>
        </div>
        <div class="input ">
            <i class="fa fa-calendar-plus-o " title="" data-toggle="tooltip"
               data-original-title="Дополнительные дни поиска"></i>&nbsp;
            <input type="number" v-model="additionalBegin" class="input-xxs">
        </div>
        <div class="input ">
            <i class="fa fa-calendar-plus-o " title="" data-toggle="tooltip"
               data-original-title="Дополнительные дни поиска"></i>&nbsp;
            <input type="number" v-model="additionalEnd" class="input-xxs">
        </div>

        <div class="input"><i class="fa fa-male" title="" data-toggle="tooltip" data-original-title="Взрослые"></i>&nbsp;
            <input type="number" required="required" min="0"
                max="6" class="input-xxs" v-model="adults">
        </div>

        <div class="input children_age_holder_parent">
            <i class="fa fa-child" title="" data-toggle="tooltip" data-original-title="Дети"></i>&nbsp;
            <input type="number" required="required" min="0" max="6" class="input-xxs" v-model="children">
            &nbsp;
            <i class="fa fa-sort-numeric-asc" title="" data-toggle="tooltip" data-original-title="Возраст детей"></i>
            <div v-if="children" class="children_age_holder_vue">
                <div>
                    <ChildrenAgeInput v-for="key in children" :key="key-1" />
                    <!---->
                    <!--<select class="plain-html">-->
                        <!--<option value="0">0</option>-->
                        <!--<option value="1">1</option>-->
                    <!--</select>-->
                </div>
            </div>
        </div>

    </div>


</template>

<script lang="ts">
    import RangePicker from './RangePicker.vue'
    import ChildrenAgeInput from './ChildrenAgeInput.vue'
    import {mapState} from 'vuex';

    export default {
        name: "Form",

        components: {
            RangePicker,
            ChildrenAgeInput
        },
        computed: {
            additionalBegin: {
                get() {
                    return this.$store.state.form.additionalBegin
                },
                set(value) {
                    this.$store.commit('form/setAdditionalBegin', value)
                }
            },
            additionalEnd: {
                get() {
                    return this.$store.state.form.additionalEnd
                },
                set(value) {
                    this.$store.commit('form/setAdditionalEnd', value)
                }
            },
            adults: {
                get() {
                    return this.$store.state.form.adults
                },
                set(value) {
                    this.$store.commit('form/setAdults', value)
                }
            },
            children: {
                get() {
                    return this.$store.state.form.children
                },
                set(value) {
                    this.$store.commit('form/setChildren', value)
                }
            },
            ...mapState({
                begin: state => state.form.begin,
                end: state => state.form.end
            })
        },
    }
</script>

<style scoped lang="scss">
    .data-form > .input {
        display: inline-block;
    }

    .input-xxs {
        width: 40px !important;

    }

    .children_age_holder_vue {
        position: absolute;
        width: 380px;
        height: 25px;
        top: -30px;
        left: 10px;

        & > div {
            display: inline-flex;
        }

        .children_age_select {
            width: 40px !important;
        }
    }
</style>


<!--<div class="input"><i class="fa fa-bed" title="" data-toggle="tooltip" data-original-title="Тип номера"></i>&nbsp;<select-->
<!--id="search_conditions_roomTypes" name="search_conditions[roomTypes][]" multiple=""-->
<!--class="select2 select2-hidden-accessible" tabindex="-1" aria-hidden="true">-->
<!--<optgroup label="Пансионат Азовский">-->
<!--<option value="allrooms_56fbd22174eb5383728b4567">Все номера</option>-->
<!--<option value="5704bf2074eb533a108b456c">Комфорт двухкомнатный</option>-->
<!--<option value="5703b5fe74eb53d26c8b456f">Комфорт плюс</option>-->
<!--<option value="5703b59474eb53c66c8b456a">Номера в домиках с верандами (2-местные + 1 доп)</option>-->
<!--<option value="57a5953b84919e3754375bf2">Резерв</option>-->
<!--<option value="5703b5e474eb53c66c8b456f">Семейные апартаменты - 2-комнатные номера в коттеджах</option>-->
<!--<option value="5703c75b74eb534b6f8b4568">Стандартный двухкомнатный</option>-->
<!--<option value="5703cb4f74eb5308708b4567">Стандартный мини</option>-->
<!--<option value="5703b8a874eb53d26c8b4572">Стандартный плюс</option>-->
<!--</optgroup>-->
<!--<optgroup label="Пансионат АзовЛенд">-->
<!--<option value="allrooms_5705190e74eb53461c8b4916">Все номера</option>-->
<!--<option value="5bb36bf7cd572242b77ec2df">АЛ Deluxe</option>-->
<!--<option value="570b82f474eb5351028b4567">АЛ Комфорт плюс панорамный вид на море</option>-->
<!--<option value="570b970474eb5391058b4567">АЛ Номер стандарт</option>-->
<!--<option value="57051da574eb53441c8b484f">АЛ Семейные апартаменты 2-комнатные панорамный вид на море-->
<!--</option>-->
<!--<option value="57051d9474eb53441c8b484d">АЛ Семейные номера панорамный вид на море</option>-->
<!--<option value="57051d3b74eb53441c8b4844">АЛ Стандарт мини в домиках</option>-->
<!--<option value="57051d5274eb53441c8b4847">АЛ номера комфорт в домиках</option>-->
<!--<option value="57051d7574eb53441c8b4849">АЛ номера комфорт в корпусах</option>-->
<!--<option value="57051d8674eb53441c8b484b">АЛ номера комфорт плюс</option>-->
<!--</optgroup>-->
<!--</select><span class="select2 select2-container select2-container&#45;&#45;default" dir="ltr"-->
<!--style="width: 250px;"><span class="selection"><span-->
<!--class="select2-selection select2-selection&#45;&#45;multiple" role="combobox" aria-haspopup="true"-->
<!--aria-expanded="false" tabindex="-1"><ul class="select2-selection__rendered"><li-->
<!--class="select2-search select2-search&#45;&#45;inline"><input class="select2-search__field" type="search"-->
<!--tabindex="0" autocomplete="off" autocorrect="off"-->
<!--autocapitalize="none" spellcheck="false"-->
<!--role="textbox" aria-autocomplete="list"-->
<!--placeholder="Сделайте выбор" style="width: 248px;"></li></ul></span></span><span-->
<!--class="dropdown-wrapper" aria-hidden="true"></span></span>-->
<!--</div>-->


<!--<div class="input"><i class="fa fa-file-text-o" title="" data-toggle="tooltip"-->
<!--data-original-title="Номер заказа"></i>&nbsp;-->
<!--<input type="number" id="search_conditions_order" name="search_conditions[order]" class="input-xs only-int form-control input-sm">-->
<!--</div>-->
<!---->
<!--<div class="input">-->
<!--<i class="fa fa-exclamation-circle" title="" data-toggle="tooltip"-->
<!--data-original-title="Игнорировать условия и ограничения"></i>&nbsp;-->
<!--<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-off bootstrap-switch-small bootstrap-switch-animate bootstrap-switch-id-search_conditions_isForceBooking">-->
<!--<div class="bootstrap-switch-container"><span-->
<!--class="bootstrap-switch-handle-on bootstrap-switch-success">да</span><label-->
<!--for="search_conditions_isForceBooking" class="bootstrap-switch-label">&nbsp;</label><span-->
<!--class="bootstrap-switch-handle-off bootstrap-switch-default">нет</span>-->
<!--<input type="checkbox" id="search_conditions_isForceBooking" name="search_conditions[isForceBooking]" value="1">-->
<!--</div>-->
<!--</div>-->
<!--</div>-->
<!---->
<!--<div class="input"><i class="fa fa-star" title="" data-toggle="tooltip"-->
<!--data-original-title="Строгое соответствие спец. предложений"></i>&nbsp;-->
<!--<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-off bootstrap-switch-small bootstrap-switch-animate bootstrap-switch-id-search_conditions_isSpecialStrict">-->
<!--<div class="bootstrap-switch-container"><span-->
<!--class="bootstrap-switch-handle-on bootstrap-switch-success">да</span><label-->
<!--for="search_conditions_isSpecialStrict" class="bootstrap-switch-label">&nbsp;</label><span-->
<!--class="bootstrap-switch-handle-off bootstrap-switch-default">нет</span><input type="checkbox"-->
<!--id="search_conditions_isSpecialStrict"-->
<!--name="search_conditions[isSpecialStrict]"-->
<!--value="1">-->
<!--</div>-->
<!--</div>-->
<!--</div>-->
<!---->
<!---->
<!--<div class="input">-->
<!--<button class="btn btn-primary" id="searcher-sync-submit-button"><i class="fa fa-search"></i> Найти-->
<!--</button>-->
<!--</div>-->
<!---->
<!--<div class="input">-->
<!--<button class="btn btn-primary" id="searcher-submit-button"><i class="fa fa-search"></i> Найти Sync-->
<!--</button>-->
<!--</div>-->
<!---->
<!--<div class="modal fade" id="additionalSearchSetup" tabindex="-1" role="dialog"-->
<!--aria-labelledby="exampleModalCenterTitle" aria-hidden="true">-->
<!--<div class="modal-dialog modal-dialog-centered" role="document">-->
<!--<div class="modal-content">-->
<!---->
<!--<div class="modal-header"><h5 class="modal-title" id="exampleModalLongTitle">Доп настройки поиска.</h5>-->
<!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span-->
<!--aria-hidden="true">×</span></button>-->
<!--</div>-->
<!--<div class="modal-body">-->
<!--<div class="input"><i class="fa fa-envelope" title="" data-toggle="tooltip"-->
<!--data-original-title="Использовать кэш при поиске"></i>&nbsp;<div-->
<!--class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-small bootstrap-switch-animate bootstrap-switch-id-search_conditions_isUseCache">-->
<!--<div class="bootstrap-switch-container"><span-->
<!--class="bootstrap-switch-handle-on bootstrap-switch-success">да</span><label-->
<!--for="search_conditions_isUseCache"-->
<!--class="bootstrap-switch-label">&nbsp;</label><span-->
<!--class="bootstrap-switch-handle-off bootstrap-switch-default">нет</span><input-->
<!--type="checkbox" id="search_conditions_isUseCache"-->
<!--name="search_conditions[isUseCache]" value="1" checked="checked"></div>-->
<!--</div>-->
<!--</div>-->
<!--<div class="input"><a href="/app_dev.php/search/cache/flush" class="btn btn-warning" title=""-->
<!--data-toggle="tooltip" role="button"-->
<!--data-original-title="Очистить кэш, весь, совсем.">Очистка кэша</a></div>-->
<!--</div>-->
<!--<div class="modal-footer">-->
<!--<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>-->
<!--</div>-->
<!--</div>-->
<!--</div>-->
<!--</div>-->
<!---->
<!--<div class="hide">-->
<!--<div><label for="search_conditions_hotels">Hotels</label><select id="search_conditions_hotels"-->
<!--name="search_conditions[hotels][]"-->
<!--multiple=""-->
<!--class="select2 select2-hidden-accessible"-->
<!--tabindex="-1" aria-hidden="true">-->
<!--<option value="56fbd22174eb5383728b4567">Пансионат Азовский</option>-->
<!--<option value="5704eb4474eb5356108b4845">Парк - отель "РИО"</option>-->
<!--<option value="5705190e74eb53461c8b4916">Пансионат АзовЛенд</option>-->
<!--<option value="5bb48e1bcd57222e7f51ec21">С - Зарубежные</option>-->
<!--</select><span class="select2 select2-container select2-container&#45;&#45;default" dir="ltr" style="width: 250px;"><span-->
<!--class="selection"><span class="select2-selection select2-selection&#45;&#45;multiple" role="combobox"-->
<!--aria-haspopup="true" aria-expanded="false" tabindex="-1"><ul-->
<!--class="select2-selection__rendered"><li class="select2-search select2-search&#45;&#45;inline"><input-->
<!--class="select2-search__field" type="search" tabindex="0" autocomplete="off" autocorrect="off"-->
<!--autocapitalize="none" spellcheck="false" role="textbox" aria-autocomplete="list"-->
<!--placeholder="Сделайте выбор" style="width: 100px;"></li></ul></span></span><span-->
<!--class="dropdown-wrapper" aria-hidden="true"></span></span>-->
<!--</div>-->
<!--<div><label for="search_conditions_tariffs">Tariffs</label>-->
<!--<select id="search_conditions_tariffs" name="search_conditions[tariffs][]" multiple="" class="select2 select2-hidden-accessible" tabindex="-1" aria-hidden="true">-->
<!--<option value="56fbd22274eb5383728b45ad">Основной тариф</option>-->
<!--<option value="5704eb4e74eb5383178b45c5">Основной тариф</option>-->
<!--<option value="5705190f74eb53d01e8b45c5">Основной тариф</option>-->
<!--<option value="571745d374eb53db1a8b456f">Мать и дитя</option>-->
<!--<option value="571760bd74eb536f1b8b4605">Ребенок бесплатно</option>-->
<!--<option value="5717675f74eb536f1b8b52f7">Скидка 14%</option>-->
<!--<option value="5717679874eb536f1b8b52fb">Скидка 9%</option>-->
<!--<option value="5717688f74eb536f1b8b5309">Ребенок бесплатно</option>-->
<!--<option value="571779d074eb53862e8b4578">Мать и дитя</option>-->
<!--<option value="57177b6374eb53862e8b4830">Скидка 18%</option>-->
<!--<option value="57177b7e74eb53632d8b45a9">Скидка 14%</option>-->
<!--<option value="5717836174eb539c308b456d">Ребенок бесплатно</option>-->
<!--<option value="571784c574eb530b318b4653">Мать и дитя</option>-->
<!--<option value="5717930a74eb539e308b4abb">Скидка 14%</option>-->
<!--<option value="5717932674eb530b318b49a1">Скидка 9%</option>-->
<!--<option value="57c8012984919e642651335e">СКИДКА 22% (50%)</option>-->
<!--<option value="57c8025984919e674768d116">СКИДКА 25% (100%)</option>-->
<!--<option value="57c802f884919e674768d11d">СКИДКА 22+1% (50%)</option>-->
<!--<option value="57c8036584919e674768d122">СКИДКА 25+1% (100%)</option>-->
<!--<option value="57c8071784919e642651338b">СКИДКА 20% (100%)</option>-->
<!--<option value="57c8074a84919e642651338e">СКИДКА 20+1% (100%)</option>-->
<!--<option value="57c8079984919e6746242d0c">СКИДКА 18% (50%)</option>-->
<!--<option value="57c807d084919e6746242d10">СКИДКА 18+1% (50%)</option>-->
<!--<option value="57c9715c84919e31de337b77">Акция "Один как господин"</option>-->
<!--<option value="57c97e0f84919e35e946eddb">Акция "Мать и дитя"</option>-->
<!--<option value="57cc288a84919e041d6961a7">"Мать и дитя"</option>-->
<!--<option value="57ed1c6b84919e4d1a1eec53">Основной тариф</option>-->
<!--<option value="57ff5af884919e71e03de59f">Спецпредложение (100% оплата)</option>-->
<!--<option value="582dc0fb84919e381051540f">Выключен - старый тариф см.Описание</option>-->
<!--<option value="583831b084919e7efc4d0994">Акция "Ребенок бесплатно"</option>-->
<!--<option value="5838467984919e063776caf9">Акция "Мать и дитя"</option>-->
<!--<option value="585bc7e584919e2c1d799674">Майский (с 22.12.2016)</option>-->
<!--<option value="585bde1a84919e31ab1c0dec">Домик в сентябре</option>-->
<!--<option value="5882194884919e54264212bf">СКИДКА 24% (100%)</option>-->
<!--<option value="58821a0984919e54264212c9">Скидка 24+1%</option>-->
<!--<option value="58821bc984919e53ec62a3b0">Скидка 19%</option>-->
<!--<option value="58821c4684919e54264212e9">Скидка 19+1% (20%)</option>-->
<!--<option value="58821fd984919e54ba4ee13a">Скидка 17%</option>-->
<!--<option value="5882208f84919e5426421316">Скидка 17+1%</option>-->
<!--<option value="5882214a84919e53ec62a777">СКИДКА 21% (50%)</option>-->
<!--<option value="5882219d84919e53ec62a77d">Скидка 21+1% (22%)</option>-->
<!--<option value="58870c0884919e05527e1386">Спецпредложение (50% оплата)</option>-->
<!--<option value="58a4668584919e37300e4de2">СКИДКА 18% (50%)</option>-->
<!--<option value="58a4672484919e3a9e35b7a1">СКИДКА 17% (50%)</option>-->
<!--<option value="58a467c684919e3a8031f1c4">СКИДКА 21% (100%)</option>-->
<!--<option value="58a468f484919e3a9e35b7bc">СКИДКА 20% (100%)</option>-->
<!--<option value="58a469c984919e3a9e35b7c5">СКИДКА 21+1% (100%)</option>-->
<!--<option value="58a46a5d84919e3a811c74ec">СКИДКА 20+1% (100%)</option>-->
<!--<option value="58a4aec384919e582036d155">СКИДКА 18% (100%)</option>-->
<!--<option value="58a4af1584919e582036d158">СКИДКА 16% (50%)</option>-->
<!--<option value="58a4af9184919e582036d15e">СКИДКА 16+1% (50%)</option>-->
<!--<option value="58a4b00e84919e5ae3686234">СКИДКА 15% (100%)</option>-->
<!--<option value="58a4b03384919e5ae3686237">СКИДКА 13% (50%)</option>-->
<!--<option value="58a543ff84919e191e101019">СКИДКА 18+1% (50%)</option>-->
<!--<option value="58a5475984919e19f1255043">СКИДКА 17+1% (50%)</option>-->
<!--<option value="58a54ffa84919e1f624f0b93">СКИДКА 13+1% (50%)</option>-->
<!--<option value="58a5504384919e1f4f372fa9">СКИДКА 15+1% (100%)</option>-->
<!--<option value="58a5577584919e25c42bd192">СКИДКА 18+1% (100%)</option>-->
<!--<option value="58e24412cd572245fa4cd390">СКИДКА 23% (50%)</option>-->
<!--<option value="58e2446fcd572245fa4cd394">СКИДКА 23+1% (50%)</option>-->
<!--<option value="58e244c9cd57224503000fb4">СКИДКА 25% (100%)</option>-->
<!--<option value="58e244f1cd5722465372b302">СКИДКА 25+1% (100%)</option>-->
<!--<option value="58e78570cd57222fba160dc7">Выгодный июнь</option>-->
<!--<option value="5912db67cd572224cd45afdb">Спецпредложение - июнь</option>-->
<!--<option value="5912e803cd57223a2076fd84">Спецпредложение - июнь</option>-->
<!--<option value="5912f18dcd57224d0a4b9682">Спецпредложение</option>-->
<!--<option value="591588fbcd5722211c73cbae">Гостевой</option>-->
<!--<option value="59ad6682cd572238b457018e">СКИДКА 27% (50%)</option>-->
<!--<option value="59ad6732cd572238b4570195">СКИДКА 30% (100%)</option>-->
<!--<option value="59ad67bfcd572238b457019d">СКИДКА 27+3% (50%)</option>-->
<!--<option value="59ad682dcd57221eaf25bc27">СКИДКА 30+3% (100%)</option>-->
<!--<option value="59ae7c70cd5722630537a4d0">СКИДКА 35%</option>-->
<!--<option value="59ae7cc3cd57227df533e2c5">СКИДКА 32%</option>-->
<!--<option value="59ae7d10cd5722630537a4d9">СКИДКА 32+5%</option>-->
<!--<option value="59ae7d50cd572267295f31e1">СКИДКА 35+5% (100%)</option>-->
<!--<option value="5a86ceb7cd5722034a0781ee">СКИДКА 30+2,5 % (100%)</option>-->
<!--<option value="5a9e451fcd572254cb2ef316">Основной Специальный тариф Стандарты</option>-->
<!--<option value="5a9e53f2cd572269dc33c567">СКИДКА 35+3% (100%)</option>-->
<!--<option value="5a9e7452cd57222c016e3f00">Основной - для Специальный тариф домики с верандой</option>-->
<!--<option value="5a9e79c0cd572235fb138f14">Ребенок бесплатно до 7 лет</option>-->
<!--<option value="5a9e9175cd572257ff503226">Скидка 32% для номеров с кондиционерами</option>-->
<!--<option value="5aaa37b8cd57220fca700dc7">Специальный тариф!-дочерний тариф</option>-->
<!--<option value="5aab761ccd57221bd65dee13">Скидка 30% домики</option>-->
<!--<option value="5aabb6fbcd5722105220c2d6">СКИДКА 30+3% (100%)</option>-->
<!--<option value="5ab8bf24cd57222c80337d25">Специальный тариф 35% Стандарты</option>-->
<!--<option value="5adedb9bcd572201666a8a89">Основной тариф-дочерний тариф</option>-->
<!--<option value="5adedbbccd57220311712648">Гостевой тариф2018</option>-->
<!--<option value="5aed646ccd57221d6074d02d">Гостевой Стандарты 2018</option>-->
<!--<option value="5af030bccd572244c817bf81">Гостевой тариф2018</option>-->
<!--<option value="5ba24e16cd57226c32174742">СКИДКА 35% (100%)</option>-->
<!--<option value="5ba25291cd5722117d61bdad">СКИДКА 32% (50%)</option>-->
<!--<option value="5ba25508cd57225011774adc">СКИДКА 35+5% (100%)</option>-->
<!--<option value="5ba259a8cd5722117d61beb0">СКИДКА 32+5% (50%)</option>-->
<!--<option value="5ba25b4acd57225011774b45">Основной тариф-дочерний тариф</option>-->
<!--<option value="5bb48ea5cd572226f33e02a0">Основной</option>-->
<!--</select>-->
<!--<span class="select2 select2-container select2-container&#45;&#45;default" dir="ltr" style="width: 250px;"><span-->
<!--class="selection"><span class="select2-selection select2-selection&#45;&#45;multiple" role="combobox"-->
<!--aria-haspopup="true" aria-expanded="false" tabindex="-1"><ul-->
<!--class="select2-selection__rendered"><li class="select2-search select2-search&#45;&#45;inline"><input-->
<!--class="select2-search__field" type="search" tabindex="0" autocomplete="off" autocorrect="off"-->
<!--autocapitalize="none" spellcheck="false" role="textbox" aria-autocomplete="list"-->
<!--placeholder="Сделайте выбор" style="width: 100px;"></li></ul></span></span><span-->
<!--class="dropdown-wrapper" aria-hidden="true"></span></span>-->
<!--</div>-->
<!---->
<!---->
<!--<div>-->
<!--<label for="search_conditions_tourist">form.searchType.fio</label>-->
<!--<select-->
<!--name="search_conditions[tourist]" id="search_conditions_tourist"-->
<!--class="form-control input-sm findGuest select2-hidden-accessible" tabindex="-1" aria-hidden="true">-->
<!--<option selected="" value=""></option>-->
<!--</select>-->
<!--<span class="select2 select2-container select2-container&#45;&#45;default" dir="ltr" style="width: 250px;"><span-->
<!--class="selection"><span class="select2-selection select2-selection&#45;&#45;single" role="combobox"-->
<!--aria-haspopup="true" aria-expanded="false" tabindex="0"-->
<!--aria-labelledby="select2-search_conditions_tourist-container"><span-->
<!--class="select2-selection__rendered" id="select2-search_conditions_tourist-container"></span><span-->
<!--class="select2-selection__arrow" role="presentation"><b-->
<!--role="presentation"></b></span></span></span><span class="dropdown-wrapper"-->
<!--aria-hidden="true"></span></span>-->
<!--</div>-->
<!---->
<!--<div><label for="search_conditions_isOnline">Is online</label>-->
<!--<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-off bootstrap-switch-small bootstrap-switch-animate bootstrap-switch-id-search_conditions_isOnline">-->
<!--<div class="bootstrap-switch-container"><span-->
<!--class="bootstrap-switch-handle-on bootstrap-switch-success">да</span><label-->
<!--for="search_conditions_isOnline" class="bootstrap-switch-label">&nbsp;</label><span-->
<!--class="bootstrap-switch-handle-off bootstrap-switch-default">нет</span><input-->
<!--type="checkbox" id="search_conditions_isOnline" name="search_conditions[isOnline]"-->
<!--value="1"></div>-->
<!--</div>-->
<!--</div>-->
<!---->
<!--<div><label for="search_conditions_isThisWarmUp">Is this warm up</label>-->
<!--<div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-off bootstrap-switch-small bootstrap-switch-animate bootstrap-switch-id-search_conditions_isThisWarmUp">-->
<!--<div class="bootstrap-switch-container"><span-->
<!--class="bootstrap-switch-handle-on bootstrap-switch-success">да</span><label-->
<!--for="search_conditions_isThisWarmUp" class="bootstrap-switch-label">&nbsp;</label><span-->
<!--class="bootstrap-switch-handle-off bootstrap-switch-default">нет</span><input-->
<!--type="checkbox" id="search_conditions_isThisWarmUp" name="search_conditions[isThisWarmUp]"-->
<!--value="1"></div>-->
<!--</div>-->
<!--</div>-->
<!---->
<!--<div>-->
<!--<label for="search_conditions_errorLevel">Error level</label><input type="number"-->
<!--id="search_conditions_errorLevel"-->
<!--name="search_conditions[errorLevel]"-->
<!--value="0">-->
<!--</div>-->
<!---->
<!--</div>-->
<!--</div>-->