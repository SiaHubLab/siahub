<template>
    <div class="table-responsive">
      <table class="table table-bordered table-white">
        <thead>
          <tr>
            <th v-for="key in columns"
              @click="sortBy(key)"
              :class="{ active: sortKey == key }">
              {{ key | capitalize }}
              <span class="arrow" :class="sortOrders[key] > 0 ? 'asc' : 'dsc'">
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="entry in filteredData">
            <td v-for="key in columns">
              <span v-if="key != 'actions'" v-html="getFormatter(key)(entry[key], entry)"></span>
              <span v-else>
                  <router-link v-if="getFormatter(key)(entry[key], entry).view" class="btn btn-success btn-sm" :to="getFormatter(key)(entry[key], entry).view">View</router-link>
                  <router-link v-if="getFormatter(key)(entry[key], entry).edit" class="btn btn-info btn-sm" :to="getFormatter(key)(entry[key], entry).edit">Edit</router-link>
                  <router-link v-if="getFormatter(key)(entry[key], entry).delete" class="btn btn-danger btn-sm" :to="getFormatter(key)(entry[key], entry).delete">Delete</router-link>
              </span>
            </td>
          </tr>
          <tr v-if="!filteredData.length">
              <td :colspan="columns.length" v-html="notFoundMessage"></td>
          </tr>
        </tbody>
        <tfoot>
            <tr>
              <td :colspan="columns.length">
                  <nav>
                    <ul class="pager">
                      <li :class="(this.page <= 1) ? 'disabled':''"><a @click.prevent="prevPage" href="#">Previous</a></li>
                      <li :class="(this.page >= this.pages) ? 'disabled':''"><a @click.prevent="nextPage" href="#">Next</a></li>
                    </ul>
                  </nav>
              </td>
            </tr>
            <tr class="text-center">
              <td :colspan="columns.length">
                    Rows: {{this.filteredDataLength}} / Page: {{this.page}} of {{this.pages}}
              </td>
            </tr>
        </tfoot>
      </table>
  </div>
</template>

<script>
export default {
    props: {
        data: Array,
        columns: Array,
        formatters: Object,
        filterKey: String,
        defaultSort: String,
        defaultSortOrder: String,
        notFound: String,
    },
    data: function() {
        var sortOrders = {}
        this.columns.forEach((key) => {
            sortOrders[key] = (key == this.defaultSort && this.defaultSortOrder) ? this.defaultSortOrder:1
        })
        return {
            sortKey: this.defaultSort || '',
            notFoundMessage: this.notFound || 'Nothing found :(',
            sortOrders: sortOrders,
            perpage: 10,
            page: 1
        }
    },
    watch: {
        filterKey: function(){
            this.page = 1;
        },
        data: function(){
            this.page = 1;
        }
    },
    computed: {
        filteredDataLength: function() {
            var that = this;
            var filterKey = this.filterKey && this.filterKey.toLowerCase();
            var data = this.data;
            if(data) {
                if (filterKey) {
                    data = data.filter(function(row) {
                        return that.columns.some(function(key) {
                            return String(that.getFormatter(key)(row[key], row, true)).toLowerCase().indexOf(filterKey) > -1
                        })
                    })
                }
                return data.length
            } else {
                return 0
            }
        },
        filteredData: function() {
            var that = this;
            var sortKey = this.sortKey
            var filterKey = this.filterKey && this.filterKey.toLowerCase()
            var order = this.sortOrders[sortKey] || 1
            var data = this.data

            if(data) {
                if (filterKey) {
                    data = data.filter(function(row) {
                        return that.columns.some(function(key) {
                            return String(that.getFormatter(key)(row[key], row, true)).toLowerCase().indexOf(filterKey) > -1
                        })
                    })
                }
                if (sortKey) {
                    data = data.slice().sort(function(a, b) {
                        a = that.getFormatter(sortKey)(a[sortKey], a, true)
                        b = that.getFormatter(sortKey)(b[sortKey], b, true)
                        return (a === b ? 0 : a > b ? 1 : -1) * order
                    })
                }
                data = data.slice(this.perpage*(this.page-1), this.perpage*this.page);
            }
            return data
        },
        pages: function(){
            return (this.data) ? Math.ceil(this.filteredDataLength/this.perpage):1;
        }
    },
    filters: {
        capitalize: function(str) {
            return str.charAt(0).toUpperCase() + str.slice(1)
        }
    },
    methods: {
        prevPage: function () {
            this.page--;
            if(this.page < 1){
                this.page = 1;
            }
        },
        nextPage: function () {
            this.page++;
            if(this.pages < this.page){
                this.page = this.pages;
            }
        },
        sortBy: function(key) {
            this.sortKey = key
            this.sortOrders[key] = this.sortOrders[key] * -1
        },
        getFormatter: function(key){
            if(typeof this.formatters[key] === "function"){
                return this.formatters[key];
            } else {
                return function(str){ return str };
            }
        }
    }
}
</script>
