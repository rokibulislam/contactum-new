<template>
  <div :class="{'contactum_backdrop': visibility}" class="disabled-info" v-if="modal">
    <el-dialog
        :visible.sync="isVisible"
        :before-close="close"
        :width="modal.video || modal.image ? '74%' : '30%'"
    >

      <div slot="title">
        <h4>{{!modal ? 'Field disabled' : ''}}</h4>
      </div>
        <el-row :gutter="25" class="items-center">
          <el-col v-if="modal.video || modal.image" :span="12">
            <div v-if="modal.video" class="video-wrapper mr-3">
              <iframe
                  style="width: 100%; height: 300px; border-radius: 10px;"
                  :src="modal.video"
                  :title="'Video preview'"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen
              />
            </div>
            <div v-else class="mr-3">
              <img class="w-100 img-thumb" :src="modal.image" :alt="modal.title" />
            </div>
          </el-col>

          <el-col :span="modal.video || modal.image ? 12 : 24">
            <div class="video-content text-center">
              <div v-if="!modal.video && !modal.image" class="placeholder-icon">
                <i class="el-icon-video-camera" />
              </div>
              <h3 class="mb-3 title">{{ modal.title }}</h3>
              <p class="text">{{ modal.description }}</p>
              <a class="el-button mt-2 el-button--primary" v-if="!modal.hidePro" target="_blank" :href="campaignUrl">Upgrade to PRO</a>
            </div>
          </el-col>
        </el-row>
    </el-dialog>
  </div>
</template>


<script>
export default {
  name: "ProFeature",
  props: ['visibility', 'modal', 'value'],
  data() {
    return {
      'campaignUrl': 'https://wpcontactum.com/'
    }
  },
  computed: {
    isVisible() {
      return !!this.visibility || !!this.value;
    },
    hasPro() {
      return true;
    }
  },
  methods: {
    close() {
      this.$emit('update:visibility', false);
      this.$emit('input', false);
    }
  }
}
</script>

<style scoped>
.video-wrapper {
  position: relative;
  width: 100%;
  padding-bottom: 56.25%; /* 16:9 */
  height: 0;
}
.video-wrapper iframe {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 10px;
}

.placeholder-icon {
  font-size: 50px;
  color: #bbb;
  margin-bottom: 20px;
}
.video-content {
  text-align: center;
}
</style>
