<template>
<div>
    <div v-if="error" class="alert alert-danger">
        <p v-for="err in error"><strong>Oops!</strong> Error, {{err}}</p>
    </div>
    <table class="table table-bordered table-white">
        <tbody>
            <tr v-for="field in fields" v-if="data[field.key]">
                <td>
                    {{field.name}}
                </td>
                <td>
                    <span v-if="field.type == 'text'">
                          <span v-if="typeof field.formatter === 'function'">
                              <span v-html="field.formatter(data[field.key], data)"></span>
                          </span>
                          <span v-else>
                              <span v-html="data[field.key]"></span>
                          </span>
                    </span>

                    <span v-if="field.type == 'date'">
                            {{data[field.key]}}
                    </span>

                    <span v-if="field.type == 'file'">
                          <p v-if="images[field.key]">
                              <img style="width: 50%;" :src="images[field.key]" />
                          </p>
                          <p v-if="data[field.key]" ><a class="btn btn-success" :href="data[field.key]" target="_blank">Скачать</a></p>
                    </span>

                    <span v-if="field.type == 'number'">
                          {{data[field.key]}}
                    </span>

                    <span v-if="field.type == 'editor'">
                          <span v-html="data[field.key]"></span>
                    </span>

                    <span v-if="field.type == 'select'">
                        {{field.options[data[field.key]]}}
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</template>

<script>
export default {
    props: {
        data: Object,
        fields: Object,
    },
    data: function() {
        return {
            loading: false,
            error: false,
            editorOption: {

            },
            conditionsComplete: {

            },
            images: {

            }
        }
    },
    mounted(){
        this.runCheckConds();

        _.delay(() =>{
            for(var i in this.fields){
                var field = this.fields[i];
                if(field.type == "file"){
                    this.runImage(field.key);
                }
            }
        }, 500);
    },
    methods: {
        runCheckConds(){
            for(var i in this.fields){
                var field = this.fields[i];
                this.$set(this.conditionsComplete, field.key, this.checkConds(field.conditions));
            }
        },
        checkConds(conditions){
            if(!conditions) return true;

            for(var i in conditions){
                if(conditions[i]){
                    if(this.data[i] != conditions[i]){
                        return false;
                    }
                }
            }
            return true;
        },
        runImage(key) {
            var url = this.data[key];
            var that = this;
            this.testImage(url).then((result) => {
                that.$set(that.images, key, url);
            }, function(){});

            return true;
        },
        testImage(url, timeoutT) {
            return new Promise(function(resolve, reject) {
              var timeout = timeoutT || 5000;
              var timer, img = new Image();
              img.onerror = img.onabort = function() {
                  clearTimeout(timer);
                    reject("error");
              };
              img.onload = function() {
                   clearTimeout(timer);
                   resolve("success");
              };
              timer = setTimeout(function() {
                  img.src = "//!!!!/noexist.jpg";
                  reject("timeout");
              }, timeout);
              img.src = url;
            });
        }
    }
}
</script>
