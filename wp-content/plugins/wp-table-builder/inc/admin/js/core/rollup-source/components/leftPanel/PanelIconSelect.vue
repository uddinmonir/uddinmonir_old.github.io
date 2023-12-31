<template>
	<div class="wptb-settings-row wptb-settings-middle-xs">
		<div class="wptb-settings-space-between">
			<p class="wptb-settings-item-title">{{ label }}</p>
			<div class="wptb-icon-select-wrapper">
				<div class="wptb-icon-select-display">
					<div class="wptb-icon-select-preview" @click="toggleIconDrawer" ref="iconSelectButton">
						<img :src="selectedIcon.url" />
					</div>
					<div
						v-show="openDrawer"
						class="wptb-icon-select-drawer wptb-plugin-box-shadow-md"
						:style="drawerPosition"
					>
						<div class="wptb-icon-search-wrapper">
							<input type="text" placeholder="Search for icons..." v-model.trim="debunkedFilterText" />
						</div>
						<div class="wptb-icon-previews" ref="drawerRefElement">
							<div class="wptb-icon-select-drawer-preview wptb-icon-reset" @click="setIcon('', '')"></div>
							<div
								v-for="(iconUrl, name) in fullIconList()"
								class="wptb-icon-select-drawer-preview"
								:class="{ 'wptb-icon-preview-active': selectedIcon.name === name }"
								:key="name"
							>
								<img :src="iconUrl" :title="name" :draggable="false" @click="setIcon(name, iconUrl)" />
							</div>
							<intersection-observer
								:relative-element="innerDrawerRef"
								@visible="observerVisible"
								:force-hide="observerHide"
							>
								<div class="wptb-icon-select-drawer-end"></div>
							</intersection-observer>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import IntersectionObserver from '../../components/IntersectionObserver';

export default {
	props: {
		label: {
			type: String,
			default: 'Icon Select',
		},
		icons: {
			type: Object,
			default: () => {
				return {};
			},
		},
		perPage: {
			type: Number,
			default: 20,
		},
		selectedUserIcon: {
			type: Object,
			default: () => {
				return { url: null, name: null };
			},
		},
	},
	model: {
		prop: 'selectedUserIcon',
		event: 'iconSelected',
	},
	components: { IntersectionObserver },
	data() {
		return {
			selectedIcon: {
				url: null,
				name: null,
			},
			openDrawer: false,
			innerDrawerRef: null,
			paginationIndex: 1,
			observerHide: false,
			debunkedFilterText: '',
			debunkId: -1,
			filterText: '',
			drawerPosition: {
				left: 0,
			},
		};
	},
	mounted() {
		document.addEventListener('keyup', (e) => {
			if (e.code === 'Escape' && this.openDrawer) {
				this.setDrawerState(false);
			}
		});

		this.$nextTick(() => {
			// @deprecated
			// const selectedIcon = this.selectedUserIcon;
			//
			// this.selectedIcon.name = selectedIcon === '' ? null : selectedIcon;
			// this.selectedIcon.url = selectedIcon === '' ? null : this.icons[selectedIcon];

			this.selectedIcon = this.selectedUserIcon;
		});
	},
	watch: {
		debunkedFilterText(n) {
			clearTimeout(this.debunkId);

			this.debunkId = setTimeout(() => {
				this.filterText = n;
			}, 500);
		},
		selectedIcon: {
			handler(n) {
				this.$emit('iconSelected', n);
			},
			deep: true,
		},
	},
	methods: {
		setDrawerState(state) {
			this.openDrawer = state;
		},
		fullIconList() {
			return Object.keys(this.icons)
				.filter((k) => k.includes(this.filterText))
				.slice(0, this.paginationIndex * this.perPage)
				.reduce((result, key) => {
					// eslint-disable-next-line no-param-reassign
					result[key] = this.icons[key];
					return result;
				}, {});
		},
		toggleIconDrawer() {
			this.calculateDrawerPosition();
			this.openDrawer = !this.openDrawer;
			this.innerDrawerRef = this.$refs.drawerRefElement;
		},
		setIcon(iconName, iconUrl) {
			this.selectedIcon.url = iconUrl;
			this.selectedIcon.name = iconName;
			this.toggleIconDrawer();
		},
		observerVisible() {
			this.paginationIndex += 1;
		},
		calculateDrawerPosition(xPadding = 10) {
			const buttonElement = this.$refs.iconSelectButton;
			const posObject = buttonElement.getBoundingClientRect();

			const drawerLeft = posObject.x + posObject.width + xPadding;
			const drawerTop = posObject.y;

			this.drawerPosition.top = this.toPx(drawerTop);
			this.drawerPosition.left = this.toPx(drawerLeft);
		},
		toPx(val) {
			return `${val}px`;
		},
	},
};
</script>
