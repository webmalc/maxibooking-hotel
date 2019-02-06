<template>
    <select class="plain-html ages" :value="selected" @change="updateAge">
        <option v-for="index in 13" :value="index-1">{{index-1}}</option>
    </select>
</template>

<script>
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
        methods: {
            updateAge(event) {
                const value = event.target[event.target.selectedIndex].value;
                this.$store.commit('form/setChildrenAges', {key : this.currentKey, value: Number(value)});
            }
        },
        created() {
            this.$store.commit('form/setChildrenAges', {key : this.currentKey, value: this.selected});
        },
        destroyed() {
            this.$store.commit('form/deleteChildrenAge', this.currentKey )
        }
    }
</script>

<style scoped>
    .ages {
        margin-left: 2px;
        margin-right: 2px;
    }
</style>