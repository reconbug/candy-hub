<script>
    export default {
        data() {
            return {
                groups: [],
                customerGroups: [],
                languages: [],
                request: apiRequest
            }
        },
        props: {
            method: {
                type: Object
            }
        },
        mounted() {
            this.loadLanguages();
        },
        methods: {
            /**
             * Loads languages
             * @return
             */
            loadLanguages() {
                apiRequest.send('get', 'languages', [], []).then(response => {
                    response.data.forEach(lang => {
                        this.languages.push({
                            label: lang.name,
                            value: lang.lang,
                            content: '<span class=\'flag-icon flag-icon-' + lang.iso + '\'></span> ' + lang.name
                        });
                    });
                });
            },
            getChannels(channels) {
                let arr = [];
                channels.forEach(channel => {
                    arr.push({
                        label: channel.name,
                        value: channel.handle
                    });
                });
                return arr;
            }
        }
    }
</script>

<style lang="scss">
    .group {
        border:2px solid #ebebeb;
        padding:2em;
    }
    .criteria-label, .criteria-modifier {
        line-height:40px;
        display:block;
    }
    .criteria-divider {
        margin:1em 0;
        text-align:left;
        text-transform: uppercase;
        color:#BDBDBD;
    }
</style>

<template>
    <div>
        <candy-tabs nested="true">
            <candy-tab v-for="(group, index) in method.attribute_groups.data" :name="group.name" :handle="group.id" :key="group.id" :selected="index == 0 ? true : false" dispatch="collection-details">
                    <candy-attribute-translatable
                        :languages="languages"
                        :attributes="group.attributes.data"
                        :attributeData="method.attribute_data"
                        :channels="getChannels(method.channels.data)"
                        :request="request"
                    >
                    </candy-attribute-translatable>
                    <div class="form-group">
                        <label>Carrier</label>
                        <select class="form-control selectize" v-model="method.type">
                            <option value="">-- Please select</option>
                            <option value="standard">Standard</option>
                            <option value="dhl">DHL</option>
                        </select>
                    </div>
            </candy-tab>
        </candy-tabs>
    </div>
</template>
