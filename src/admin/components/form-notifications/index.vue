<template>
  <div class="form-notification-wrap">

    <!-- ── Toolbar ── -->
    <div class="notification-toolbar">
      <div class="toolbar-left">
        <transition name="slide-fade">
          <button
            v-if="editing !== false"
            class="btn btn-ghost"
            @click.prevent="back"
          >
            <i class="el-icon-arrow-left"></i> Back to list
          </button>
        </transition>
        <h2 class="section-title">
          {{ editing !== false ? currentNotification.name || 'Edit Notification' : 'Notifications' }}
        </h2>
      </div>
      <button class="btn btn-primary" @click.prevent="addNotification">
        <i class="el-icon-plus"></i> Add Notification
      </button>
    </div>

    <!-- ── Notifications Table ── -->
    <transition name="fade" mode="out-in">
      <div v-if="!editing" key="table" class="table-view">
        <div v-if="!notifications.length" class="empty-notifications">
          <div class="empty-icon"><i class="el-icon-bell"></i></div>
          <h3>No notifications yet</h3>
          <p>Add a notification to send emails when this form is submitted.</p>
          <button class="btn btn-primary" @click.prevent="addNotification">
            <i class="el-icon-plus"></i> Add your first notification
          </button>
        </div>

        <div v-else class="notification-list">
          <div
            v-for="(notif, index) in notifications"
            :key="index"
            class="notification-row-card"
            :class="{ 'is-disabled': !notif.active }"
          >
            <div class="notif-status">
              <el-switch
                :width="36"
                :value="notif.active"
                @change="(val) => toggelNotification(index, val)"
              />
              <span class="status-label" :class="notif.active ? 'active' : 'inactive'">
                {{ notif.active ? 'Enabled' : 'Disabled' }}
              </span>
            </div>

            <div class="notif-meta">
              <span class="notif-name">{{ notif.name }}</span>
              <span class="notif-subject">{{ notif.subject }}</span>
            </div>

            <div class="notif-type-badge">
              <i class="el-icon-message"></i> Email
            </div>

            <div class="notif-actions">
              <button class="icon-btn" title="Edit" @click="editNotification(index)">
                <i class="el-icon-edit"></i>
              </button>
              <button class="icon-btn" title="Duplicate" @click="duplicateNotification(index)">
                <i class="el-icon-copy-document"></i>
              </button>
              <button
                class="icon-btn danger"
                title="Delete"
                v-if="notifications.length > 1"
                @click="deleteNotification(index)"
              >
                <i class="el-icon-delete"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Edit Form ── -->
      <div v-else-if="currentNotification" key="editor" class="editor-view">

        <!-- ── Section: General ── -->
        <div class="editor-section">
          <div class="section-eyebrow">
            <span class="section-step">1</span>
            <span class="section-label">General</span>
          </div>
          <div class="editor-section-body">
            <div class="field-row">
              <div class="field-group field-group--grow">
                <label class="field-label">
                  Notification Name
                  <span class="field-required">*</span>
                </label>
                <p class="field-hint">Internal label — only visible to you.</p>
                <el-input v-model="currentNotification.name" placeholder="e.g. Admin Alert, User Confirmation" />
              </div>
              <div class="field-group field-group--fixed">
                <label class="field-label">Type</label>
                <p class="field-hint">Notification channel.</p>
                <el-select v-model="currentNotification.type" placeholder="Select type" style="width:100%">
                  <el-option label="Email" value="email">
                    <span><i class="el-icon-message" style="margin-right:6px"></i>Email</span>
                  </el-option>
                </el-select>
              </div>
            </div>
          </div>
        </div>

        <template v-if="currentNotification.type === 'email'">

          <!-- ── Section: Sender ── -->
          <div class="editor-section">
            <div class="section-eyebrow">
              <span class="section-step">2</span>
              <span class="section-label">Sender</span>
              <span class="section-desc">Shown in the recipient's inbox as the "from" identity.</span>
            </div>
            <div class="editor-section-body">
              <div class="field-row">
                <div class="field-group field-group--half">
                  <label class="field-label">From Name</label>
                  <p class="field-hint">The display name shown in the inbox.</p>
                  <div class="input-stack">
                    <el-input v-model="currentNotification.fromName" placeholder="Your Site Name" />
                    <div class="merge-tag-row">
                      <merge_tags @insert="insertValue" field="fromName" />
                    </div>
                  </div>
                </div>
                <div class="field-group field-group--half">
                  <label class="field-label">From Address</label>
                  <p class="field-hint">Must be a verified sending address.</p>
                  <div class="input-stack">
                    <el-input type="email" v-model="currentNotification.fromAddress" placeholder="noreply@yoursite.com" />
                    <div class="merge-tag-row">
                      <merge_tags filter="email_address" @insert="insertValue" field="fromAddress" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Section: Recipients ── -->
          <div class="editor-section">
            <div class="section-eyebrow">
              <span class="section-step">3</span>
              <span class="section-label">Recipients</span>
              <span class="section-desc">Who receives this email. Use merge tags to dynamically reference form fields.</span>
            </div>
            <div class="editor-section-body">

              <div class="field-row">
                <div class="field-group field-group--half">
                  <label class="field-label">
                    To
                    <span class="field-required">*</span>
                  </label>
                  <p class="field-hint">Primary recipient. Use <code>{field_name}</code> for dynamic values.</p>
                  <div class="input-stack">
                    <el-input v-model="currentNotification.to" placeholder="{email} or admin@yoursite.com" />
                    <div class="merge-tag-row">
                      <merge_tags @insert="insertValue" field="to" />
                    </div>
                  </div>
                </div>
                <div class="field-group field-group--half">
                  <label class="field-label">Reply To</label>
                  <p class="field-hint">Replies from the recipient will go here.</p>
                  <div class="input-stack">
                    <el-input v-model="currentNotification.replyTo" placeholder="{email} or support@yoursite.com" />
                    <div class="merge-tag-row">
                      <merge_tags @insert="insertValue" field="replyTo" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="field-row field-row--compact">
                <div class="field-group field-group--half">
                  <label class="field-label">
                    CC
                    <span class="field-badge">Optional</span>
                  </label>
                  <el-input type="email" v-model="currentNotification.cc" placeholder="cc@yoursite.com" />
                </div>
                <div class="field-group field-group--half">
                  <label class="field-label">
                    BCC
                    <span class="field-badge">Optional</span>
                  </label>
                  <el-input type="email" v-model="currentNotification.bcc" placeholder="bcc@yoursite.com" />
                </div>
              </div>

            </div>
          </div>

          <!-- ── Section: Content ── -->
          <div class="editor-section">
            <div class="section-eyebrow">
              <span class="section-step">4</span>
              <span class="section-label">Email Content</span>
              <span class="section-desc">What the recipient actually reads.</span>
            </div>
            <div class="editor-section-body">

              <div class="field-group">
                <label class="field-label">
                  Subject Line
                  <span class="field-required">*</span>
                </label>
                <p class="field-hint">Keep it under 60 characters for best deliverability.</p>
                <div class="input-stack">
                  <el-input v-model="currentNotification.subject" placeholder="New submission from {first_name}" />
                  <div class="merge-tag-row">
                    <merge_tags @insert="insertValue" field="subject" />
                  </div>
                </div>
              </div>

              <div class="field-group">
                <label class="field-label">Message Body</label>
                <p class="field-hint">
                  Plain text. Use <code>{all_fields}</code> to include all submitted values,
                  or specific field tags like <code>{email}</code>, <code>{first_name}</code>.
                </p>
                <div class="input-stack input-stack--textarea">
                  <el-input
                    type="textarea"
                    :rows="10"
                    v-model="currentNotification.message"
                    placeholder="Hi there,

A new submission was received.

{all_fields}

— Your Site"
                  />
                  <div class="merge-tag-row merge-tag-row--below-textarea">
                    <merge_tags @insert="insertValue" field="message" />
                  </div>
                </div>
              </div>

            </div>
          </div>

        </template>

        <!-- ── Footer ── -->
        <div class="editor-footer">
          <button class="btn btn-ghost" @click="back">
            <i class="el-icon-close"></i> Cancel
          </button>
          <button class="btn btn-primary" @click="updateNotification">
            <i class="el-icon-check"></i> Save Notification
          </button>
        </div>

      </div>
    </transition>

  </div>
</template>

<script>
import merge_tags from "../merge-tags/index.vue";
import wp_editor from '../common/wp-editor.vue';

export default {
  name: "form_notifications",
  components: { merge_tags, wp_editor },
  data() {
    return {
      editing: false,
      editingIndex: 0,
    };
  },
  computed: {
    notifications() {
      return this.$store.state.notifications;
    },
    currentNotification() {
      return this.notifications[this.editingIndex] ?? null;
    },
  },
  methods: {
    addNotification() {
      this.$store.commit("addNotification", contactum.defaultNotification);
      this.editingIndex = this.notifications.length - 1;
      this.editing = true;
    },
    editNotification(index) {
      this.editingIndex = index;
      this.editing = true;
    },
    updateNotification() {
      this.$store.commit("updateNotification", {
        index: this.editingIndex,
        value: this.notifications[this.editingIndex],
      });
      this.editing = false;
      this.$emit('save-notification');
      this.$notify?.success({ message: 'Notification saved.', position: 'bottom-right' });
    },
    deleteNotification(index) {
      this.$confirm('Delete this notification? This cannot be undone.', 'Delete Notification', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        this.$store.commit("deleteNotification", index);
        this.$emit('save-notification');
      }).catch(() => {});
    },
    duplicateNotification(index) {
      this.$store.commit("duplicateNotification", index);
      this.$emit('save-notification');
    },
    toggelNotification(index, val) {
      this.$store.commit("updateNotificationProperty", { index, property: "active", value: val });
      this.$emit('save-notification');
    },
    insertValue(type, field, property) {
      const notification = this.notifications[this.editingIndex];
      const value = field !== undefined ? `{${type}:${field}}` : `{${type}}`;
      notification[property] = (notification[property] || '') + value;
    },
    back() {
      this.editing = false;
    },
  },
};
</script>

<style scoped lang="scss">
/* ── Tokens (inherit from parent or redefine locally) ── */
$primary:       #4f46e5;
$primary-light: #ede9fe;
$primary-dark:  #3730a3;
$danger:        #ef4444;
$danger-light:  #fee2e2;
$surface:       #ffffff;
$surface-alt:   #f8f8fb;
$border:        #e4e4f0;
$text:          #111827;
$text-muted:    #6b7280;
$radius:        10px;
$transition:    0.16s cubic-bezier(.4,0,.2,1);

/* ── Layout ── */
.form-notification-wrap {
  padding: 28px 32px;
  font-family: 'DM Sans', 'Segoe UI', system-ui, sans-serif;
  max-width: 1100px;
}

/* ── Toolbar ── */
.notification-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  gap: 12px;

  .toolbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .section-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: $text;
  }
}

/* ── Buttons ── */
%btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 18px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
  outline: none;
  transition: all $transition;
  text-decoration: none;
  white-space: nowrap;
}

.btn { @extend %btn; }

.btn-primary {
  background: $primary;
  color: #fff;
  border-color: $primary;
  box-shadow: 0 2px 8px rgba(79,70,229,.28);

  &:hover {
    background: $primary-dark;
    border-color: $primary-dark;
    box-shadow: 0 4px 14px rgba(79,70,229,.38);
    transform: translateY(-1px);
  }
  &:active { transform: translateY(0); }
}

.btn-ghost {
  background: $surface;
  color: $text-muted;
  border-color: $border;

  &:hover {
    color: $text;
    border-color: darken($border, 8%);
  }
}

/* ── Empty state ── */
.empty-notifications {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 64px 32px;
  border: 2px dashed $border;
  border-radius: 16px;
  background: $surface;
  text-align: center;

  .empty-icon {
    width: 60px;
    height: 60px;
    background: $primary-light;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: $primary;
    margin-bottom: 4px;
  }

  h3 { margin: 0; font-size: 16px; font-weight: 700; color: $text; }
  p  { margin: 0; font-size: 13px; color: $text-muted; }
}

/* ── Notification list ── */
.notification-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.notification-row-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 18px;
  background: $surface;
  border: 1px solid $border;
  border-radius: $radius;
  transition: box-shadow $transition, border-color $transition;

  &:hover {
    border-color: lighten($primary, 20%);
    box-shadow: 0 4px 16px rgba(0,0,0,.07);
  }

  &.is-disabled {
    opacity: .6;
  }
}

.notif-status {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;

  .status-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;

    &.active   { color: #16a34a; }
    &.inactive { color: $text-muted; }
  }
}

.notif-meta {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;

  .notif-name {
    font-size: 14px;
    font-weight: 600;
    color: $text;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .notif-subject {
    font-size: 12px;
    color: $text-muted;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}

.notif-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 10px;
  background: $primary-light;
  color: $primary;
  border-radius: 99px;
  font-size: 11px;
  font-weight: 600;
  flex-shrink: 0;
}

.notif-actions {
  display: flex;
  gap: 4px;
  flex-shrink: 0;
}

.icon-btn {
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid $border;
  border-radius: 6px;
  background: $surface;
  color: $text-muted;
  cursor: pointer;
  font-size: 13px;
  transition: all $transition;

  &:hover {
    border-color: $primary;
    color: $primary;
    background: $primary-light;
  }

  &.danger:hover {
    border-color: $danger;
    color: $danger;
    background: $danger-light;
  }
}

/* ══════════════════════════════════════
   EDITOR VIEW
══════════════════════════════════════ */
.editor-view {
  border: 1px solid $border;
  border-radius: 14px;
  overflow: hidden;
  background: $surface-alt;
}

/* ── Section shell ── */
.editor-section {
  background: $surface;
  border-bottom: 1px solid $border;

  &:last-of-type { border-bottom: none; }
}

/* ── Section eyebrow header ── */
.section-eyebrow {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 28px;
  border-bottom: 1px solid $border;
  background: $surface-alt;
}

.section-step {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: $primary;
  color: #fff;
  font-size: 11px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  letter-spacing: 0;
}

.section-label {
  font-size: 12px;
  font-weight: 700;
  color: $text;
  text-transform: uppercase;
  letter-spacing: .07em;
}

.section-desc {
  font-size: 12px;
  color: $text-muted;
  margin-left: 4px;
  font-weight: 400;
  text-transform: none;
  letter-spacing: 0;
}

/* ── Section body ── */
.editor-section-body {
  padding: 24px 28px 8px;
}

/* ── Field rows ── */
.field-row {
  display: flex;
  gap: 20px;
  width: 100%;

  &--compact { margin-top: -4px; }
}

/* ── Field groups ── */
.field-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 22px;

  &--half  { flex: 1; min-width: 0; }
  &--grow  { flex: 1; min-width: 0; }
  &--fixed { width: 210px; flex-shrink: 0; }
}

/* ── Labels ── */
.field-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  color: $text;
  margin-bottom: 3px;
  line-height: 1.3;
}

/* Required asterisk */
.field-required {
  color: #e53e3e;
  font-size: 13px;
  line-height: 1;
}

/* "Optional" pill badge */
.field-badge {
  display: inline-flex;
  align-items: center;
  padding: 1px 7px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 99px;
  font-size: 10px;
  font-weight: 600;
  color: $text-muted;
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-left: 2px;
}

/* Hint text below label */
.field-hint {
  margin: 0 0 8px;
  font-size: 12px;
  color: $text-muted;
  line-height: 1.5;

  code {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 1px 5px;
    font-size: 11px;
    font-family: 'Fira Code', 'Cascadia Code', monospace;
    color: $primary;
  }
}

/* ── Input + merge tags stacked ── */
.input-stack {
  display: flex;
  flex-direction: column;
  gap: 0;

  /* Visually connect input and merge-tag strip */
  .el-input__inner,
  .el-textarea__inner {
    border-bottom-left-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
  }

  &--textarea .el-textarea__inner {
    border-bottom-left-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
  }
}

/* Merge tag row — flush strip attached below input */
.merge-tag-row {
  background: #f8f9fc;
  border: 1px solid $border;
  border-top: none;
  border-radius: 0 0 8px 8px;
  padding: 6px 10px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;

  &--below-textarea {
    border-radius: 0 0 8px 8px;
  }
}

/* ── Editor footer ── */
.editor-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 10px;
  padding: 16px 28px;
  border-top: 1px solid $border;
  background: $surface-alt;
}

/* ── Transitions ── */
.fade-enter-active,
.fade-leave-active { transition: opacity .18s ease; }
.fade-enter,
.fade-leave-to    { opacity: 0; }

.slide-fade-enter-active { transition: all .18s ease; }
.slide-fade-leave-active { transition: all .14s ease; }
.slide-fade-enter,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-8px);
}
</style>