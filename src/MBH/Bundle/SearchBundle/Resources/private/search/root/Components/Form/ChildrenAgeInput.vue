<template>
    <select class="plain-html ages" :value="selected" @change="updateAge">
        <option v-for="index in 13" :value="index-1">{{index-1}}</option>
    </select>
</template>

<script lang="ts">

    import {mapMutations} from 'vuex';

    export default {
        name: "ChildrenAgeInput",
        computed: {
            selected: function () {
                const key = this.$vnode.key;
                return this.$store.state.form.childrenAges[key] || 0;
            },
            currentKey() {
                return this.$vnode.key;
            }
        },
        created() {
            this.setChildrenAges({key : this.currentKey, value: this.selected});
        },
        destroyed() {
            this.deleteChildrenAge(this.currentKey);
        },
        methods: {
            updateAge(event) {
                const value = event.target[event.target.selectedIndex].value;
                this.setChildrenAges({key : this.currentKey, value: Number(value)});
            },
            ...mapMutations('form', ['setChildrenAges', 'deleteChildrenAge'])

        }
    }
</script>

<style scoped>
    .ages {
        margin-left: 2px;
        margin-right: 2px;
    }
</style>