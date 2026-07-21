<template>
  <div class="oem-home">

    <!-- ═══════════ Hero Section ═══════════ -->
    <section class="hero-section">
      <h1 class="hero-title">OEM Partner Programs</h1>
      <div class="breadcrumb">
        <a class="breadcrumb-link"><strong>Home</strong></a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-current">OEM Programs</span>
      </div>
    </section>

    <!-- ═══════════ About / Intro Section ═══════════ -->
    <section class="about-section">
      <p class="pre-header">OEM Programs</p>
      <h2 class="about-heading">Your OEM Certified Marketing Experts.</h2>
      <p class="about-text">
        Over the last 15 years, we've created a proven, repeatable approach to automotive advertising
        that works regardless of market conditions. Our team of experienced marketers believes every
        automotive OEM brand has unique value propositions, and our job is to discover that point of
        difference and deliver it to the right consumers. Our mission is to evolve the brand to consumer
        buying experience.
      </p>
      <p class="about-text">
        Whether you're a dealership growing your market share or trying to stand out among the competition;
        we have the team, tech-enabled products and innovative strategies to help any OEM grow their business.
        That's why these top automotive brands have chosen Force Marketing and our Family of Brands as a
        Certified Advertising Partner. We offer customized programs to each OEMs guidelines that help dealers
        across the nation maximize their ad dollars with approved Co-op funding.
      </p>
      <p class="about-cta">Learn more about each of our custom designed OEM programs below.</p>
    </section>

    <!-- ═══════════ Solutions Table ═══════════ -->
    <section class="table-section">
      <div class="table-wrap">
        <table class="custom-table">
          <thead>
            <tr>
              <th colspan="2">Solutions</th>
              <th><img :src="brandLogos.stellantis" alt="Stellantis" /></th>
              <th><img :src="brandLogos.kia" alt="KIA" /></th>
              <th><img :src="brandLogos.bmw" alt="BMW MINI" /></th>
              <th><img :src="brandLogos.infiniti" alt="INFINITI" /></th>
              <th><img :src="brandLogos.mazda" alt="Mazda" /></th>
              <th><img :src="brandLogos.jlr" alt="Jaguar Land Rover" /></th>
              <th><img :src="brandLogos.audi" alt="Audi" /></th>
              <th><img :src="brandLogos.subaru" alt="Subaru" /></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><img :src="solutionLogos.drive" alt="Drive" /></td>
              <td>Streaming Video &amp; Audio</td>
              <td>✓</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td>
            </tr>
            <tr>
              <td><img :src="solutionLogos.recapture" alt="Recapture" /></td>
              <td>Lost Shopper Identification</td>
              <td>✓</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td>
            </tr>
            <tr>
              <td><img :src="solutionLogos.atom" alt="Atom" /></td>
              <td>Fixed-Ops Marketing</td>
              <td>✓</td><td></td><td>✓</td><td></td><td>✓</td><td></td><td></td><td></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ═══════════ Services Table ═══════════ -->
    <section class="table-section">
      <div class="table-wrap">
        <table class="custom-table">
          <thead>
            <tr>
              <th colspan="2">Services</th>
              <th><img :src="brandLogos.stellantis" alt="Stellantis" /></th>
              <th><img :src="brandLogos.kia" alt="KIA" /></th>
              <th><img :src="brandLogos.bmw" alt="BMW MINI" /></th>
              <th><img :src="brandLogos.infiniti" alt="INFINITI" /></th>
              <th><img :src="brandLogos.mazda" alt="Mazda" /></th>
              <th><img :src="brandLogos.jlr" alt="Jaguar Land Rover" /></th>
              <th><img :src="brandLogos.audi" alt="Audi" /></th>
              <th><img :src="brandLogos.subaru" alt="Subaru" /></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="svc in servicesData" :key="svc.name">
              <td>{{ svc.name }}</td>
              <td>{{ svc.desc }}</td>
              <td v-for="(val, i) in svc.checks" :key="i">{{ val ? '✓' : '' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ═══════════ Brand Links ═══════════ -->
    <section class="brand-links-section">
      <ul class="brand-links-list">
        <li v-for="(name, idx) in brandNames" :key="idx">{{ name }}</li>
      </ul>
    </section>

    <!-- ═══════════ Portrait Cards Gallery — Row 1 ═══════════ -->
    <section class="gallery-section">
      <div class="gallery-grid">
        <div class="gallery-card" v-for="(card, idx) in galleryCards" :key="idx">
          <img :src="card.img" :alt="card.name" class="gallery-img" />
        </div>
      </div>
    </section>

    <!-- ═══════════ CTA Section — OUR BRANDS ═══════════ -->
    <section class="cta-section">
      <p class="cta-heading"><strong>OUR BRANDS</strong></p>
      <h4 class="cta-subheading">
        Explore Force Marketing's rich portfolio of brands that provide dealers with a
        new front-to-back consumer lifecycle marketing solution.
      </h4>
      <button class="cta-btn" @click="$router.push('/tel')">
        <span>Contact Us Today!</span>
      </button>

      <div class="cta-separator"></div>
      <div class="cta-spacer"></div>

      <div class="cta-logos">
        <img :src="brandImgs.force" alt="Force Marketing" class="cta-logo-img" />
        <img :src="brandImgs.wedrive" alt="WeDrive Auto" class="cta-logo-img" />
        <img :src="brandImgs.gsm" alt="GSM" class="cta-logo-img" />
      </div>
    </section>

    <!-- ═══════════ Footer ═══════════ -->
    <footer class="oem-footer">
      <div class="footer-grid">
        <!-- Col 1: Logo + Tagline + Social -->
        <div class="footer-col footer-col--brand">
          <img :src="footerLogo" class="footer-logo-img" alt="Force Marketing" />
          <p class="footer-tagline">
            Through our tech-driven products and solutions, it's our mission to transform
            the brand-to-consumer buying experience.
          </p>
          <div class="footer-socials">
            <a v-for="icon in socialIcons" :key="icon.name" class="social-icon-link" :title="icon.name">
              <span>{{ icon.label }}</span>
            </a>
          </div>
        </div>

        <!-- Col 2: Thumbnails -->
        <div class="footer-col footer-col--thumbs">
          <div class="footer-thumb">
            <img :src="thumbImgs.culture" alt="Culture" />
          </div>
          <div class="footer-thumb">
            <img :src="thumbImgs.oem" alt="OEM Certified" />
          </div>
        </div>

        <!-- Col 3: OEM Certified Partners -->
        <div class="footer-col footer-col--links">
          <h4 class="footer-heading">OEM Certified Partners</h4>
          <ul class="footer-link-list">
            <li v-for="name in oemPartners" :key="name">{{ name }}</li>
          </ul>
        </div>

        <!-- Col 4: About Us -->
        <div class="footer-col footer-col--links">
          <h4 class="footer-heading">About Us</h4>
          <ul class="footer-link-list">
            <li v-for="item in aboutLinks" :key="item">{{ item }}</li>
          </ul>
        </div>

        <!-- Col 5: Get Connected -->
        <div class="footer-col footer-col--contact">
          <h4 class="footer-heading">Get Connected</h4>
          <ul class="footer-contact-list">
            <li><strong>Phone:</strong> (866) 854-2815</li>
            <li><strong>Email:</strong> info@forcemktg.com</li>
            <li><strong>Address:</strong> 3098 Piedmont Rd NE, 4th Floor, Atlanta, Ga. 30305</li>
          </ul>
          <button class="footer-contact-btn" @click="$router.push('/tel')">CONTACT US</button>
        </div>
      </div>

      <!-- Bottom bar -->
      <div class="footer-bottom">
        <p>&copy; 2025 Force Marketing | All Rights Reserved | Privacy Policy</p>
      </div>
    </footer>

    <!-- ═══════════ Gift Package ═══════════ -->
    <GiftPackage v-model="showGift" />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import store from '@/store/index'
import GiftPackage from '@/components/gift/index.js'

export default {
  components: { GiftPackage },
  setup() {
    const showGift = ref(false)

    store.dispatch('changefooCheck', 'home')

    onMounted(() => {
      showGift.value = true
    })

    // ── Brand logos for table headers ──
    const brandLogos = {
      stellantis: require('@/assets/images/oem/BrandLogos-Stellantis.png'),
      kia: require('@/assets/images/oem/Logo-Kiax2.png'),
      bmw: require('@/assets/images/oem/Logo-BMW_MINI.png'),
      infiniti: require('@/assets/images/oem/Logo-INFINITI.png'),
      mazda: require('@/assets/images/oem/OEMPartnerLogos-MAZDA.png'),
      jlr: require('@/assets/images/oem/Logo-JLR-2024.png'),
      audi: require('@/assets/images/oem/BrandLogos-Audi.png'),
      subaru: require('@/assets/images/oem/4c_Vertical_KO.png'),
    }

    // ── Solution logos ──
    const solutionLogos = {
      drive: require('@/assets/images/oem/Drive_Logo.png'),
      recapture: require('@/assets/images/oem/Recapture_Logo.png'),
      atom: require('@/assets/images/oem/Atom_Logo.png'),
    }

    // ── Services table data ──
    const servicesData = [
      { name: 'Paid Search', desc: 'New, Pre-owned & Service PPC Ads', checks: [true, true, true, true, true, true, true, true] },
      { name: 'Display', desc: 'Retargeting & Behavioral', checks: [true, true, true, true, true, true, true, true] },
      { name: 'Social', desc: 'Retargeting, Behavioral & Inventory', checks: [true, true, true, true, true, true, true, true] },
      { name: 'Video', desc: 'Retargeting, Behavioral & Inventory', checks: [true, true, true, true, true, true, true, true] },
      { name: 'SEO', desc: 'Management & Monthly Content', checks: [true, false, true, false, true, true, true, true] },
      { name: 'Email', desc: 'Cleaned Database Audiences', checks: [true, false, true, false, true, true, true, false] },
      { name: 'Cars For Sale', desc: 'Engaged User, Retargeting & Behavioral', checks: [true, false, true, false, true, false, true, false] },
      { name: 'OTT/CTV', desc: 'Addressable & Connected TV', checks: [true, true, true, true, false, false, true, true] },
      { name: 'Streaming Audio', desc: 'Retargeting & Behavioral', checks: [true, true, true, true, false, false, true, true] },
    ]

    // ── Brand names for links ──
    const brandNames = ['Kia', 'Audi', 'Stellantis', 'Infiniti', 'Jaguar Land Rover', 'BMW Mini', 'Mazda', 'Subaru']

    // ── Portrait gallery cards ──
    const galleryCards = [
      { img: require('@/assets/images/oem/Image1-KIA.png'), name: 'KIA' },
      { img: require('@/assets/images/oem/Image2-AUDI.png'), name: 'Audi' },
      { img: require('@/assets/images/oem/Image3-STELLANTIS.png'), name: 'Stellantis' },
      { img: require('@/assets/images/oem/Image4-INFINITI.png'), name: 'Infiniti' },
      { img: require('@/assets/images/oem/Image5-JLR.png'), name: 'Jaguar Land Rover' },
      { img: require('@/assets/images/oem/Image6-BMWMINI.png'), name: 'BMW Mini' },
      { img: require('@/assets/images/oem/Image7-MAZDA.png'), name: 'Mazda' },
      { img: require('@/assets/images/oem/Image8-Subaru.png'), name: 'Subaru' },
      { img: require('@/assets/images/oem/Force-GM-OEMPAge.png'), name: 'GM Canada' },
    ]

    // ── CTA brand logos ──
    const brandImgs = {
      force: require('@/assets/images/oem/Force-Marketing(1).png'),
      wedrive: require('@/assets/images/oem/wd2.png'),
      gsm: require('@/assets/images/oem/gsm-product.png'),
    }

    // ── Footer ──
    const footerLogo = require('@/assets/images/shiftlogo-header.svg')

    const socialIcons = [
      { name: 'Facebook', label: 'f' },
      { name: 'Twitter', label: '𝕏' },
      { name: 'LinkedIn', label: 'in' },
      { name: 'YouTube', label: '▶' },
      { name: 'Instagram', label: '◉' },
    ]

    const thumbImgs = {
      culture: require('@/assets/images/home/banner1.webp'),
      oem: require('@/assets/images/home/banner2.webp'),
    }

    const oemPartners = ['Audi', 'BMW MINI', 'INFINITI', 'Jaguar Land Rover', 'KIA', 'Mazda', 'Stellantis']
    const aboutLinks = ['Our Brands', 'Force Marketing', 'WeDrive Auto', 'GSM', 'Culture', 'Leadership', 'Join Us']

    return {
      showGift,
      brandLogos,
      solutionLogos,
      servicesData,
      brandNames,
      galleryCards,
      brandImgs,
      footerLogo,
      socialIcons,
      thumbImgs,
      oemPartners,
      aboutLinks,
    }
  }
}
</script>

<style lang="scss" scoped>
@import '@/styles/theme.scss';

// ═══════════════════════════════════════════
// IMPORTANT: postcss-pxtorem converts px→rem with rootValue=37.5
// Actual root font-size = 18px (from setRem in App.vue)
// Rendered size = declared_px / 37.5 * 18 = declared_px * 0.48
// So declared_px = desired_px * 37.5 / 18 ≈ desired_px * 2.08
// ═══════════════════════════════════════════

.oem-home {
  display: block !important;
  min-height: 100%;
  background: $bg-primary;
  padding-bottom: 120px;
  overflow-x: hidden;
  max-width: 100%;
}

/* ═══════════ Hero ═══════════ */
.hero-section {
  padding: 120px 0 60px;
  text-align: left;
}

.hero-title {
  font-size: 62px;       /* renders ~30px */
  font-weight: 900;
  color: #fff;
  margin: 0 0 20px;
  line-height: 1em;
  padding: 0 40px;
}

.breadcrumb {
  font-size: 34px;       /* renders ~16px */
  color: $textMuted;
  padding: 0 40px;
}

.breadcrumb-link {
  color: $textSecondary;
  cursor: pointer;
  text-decoration: none;

  strong { font-weight: 700; }
}

.breadcrumb-sep {
  margin: 0 16px;
  color: $textMuted;
}

.breadcrumb-current {
  color: $textSecondary;
}

/* ═══════════ About Section ═══════════ */
.about-section {
  padding: 120px 40px 80px;
  text-align: center;
}

.pre-header {
  font-size: 38px;       /* renders ~18px */
  color: $theme;
  margin: 0 0 20px;
  text-transform: uppercase;
  font-weight: 600;
}

.about-heading {
  font-size: 54px;       /* renders ~26px */
  font-weight: 900;
  color: #fff;
  margin: 0 auto 40px;
  line-height: 1em;
  max-width: 800px;
}

.about-text {
  font-size: 38px;       /* renders ~18px */
  color: $textSecondary;
  line-height: 1.5;
  margin: 0 auto 40px;
  max-width: 800px;
}

.about-cta {
  font-size: 38px;       /* renders ~18px */
  color: $textColor;
  font-weight: 600;
  margin: 32px 0 0;
}

/* ═══════════ Tables ═══════════ */
.table-section {
  padding: 0 0 80px;
}

.table-wrap {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;

  &::-webkit-scrollbar { display: none; }
}

.custom-table {
  width: 100%;
  min-width: 1700px;
  border-collapse: collapse;
  color: $textColor;

  thead {
    th {
      padding: 30px 20px;
      text-align: center;
      font-weight: 700;
      color: #fff;
      font-size: 34px;     /* renders ~16px */
      letter-spacing: 6px;
      text-transform: uppercase;
      border-bottom: 2px solid $border;
      vertical-align: middle;
      background: #273b4d;
      min-width: 240px;

      &:first-child {
        text-align: left;
        padding-left: 30px;
      }

      img {
        max-height: 100px;
        max-width: 200px;
        width: auto;
        object-fit: contain;
      }
    }
  }

  tbody {
    tr {
      &:nth-child(even) td {
        background: rgba(255, 255, 255, 0.03);
      }
      &:nth-child(odd) td {
        background: rgba(255, 255, 255, 0.06);
      }
    }

    td {
      padding: 28px 20px;
      text-align: center;
      vertical-align: middle;
      color: $textSecondary;
      font-size: 36px;     /* renders ~17px */
      border-top: 4px solid $border;

      &:first-child {
        text-align: left;
        padding-left: 30px;
        font-weight: 600;
        font-size: 36px;
        max-width: 200px;
      }

      &:nth-child(2) {
        font-size: 36px;
      }

      img {
        width: 100%;
        max-width: 100px;
        height: auto;
      }
    }
  }
}

/* ═══════════ Brand Links ═══════════ */
.brand-links-section {
  padding: 0 40px 80px;
}

.brand-links-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;

  li {
    font-size: 46px;       /* renders ~22px */
    color: $textColor;
    cursor: pointer;
    text-transform: uppercase;
    font-weight: 600;
    padding: 10px 0;

    &:active { color: $theme; }
  }
}

/* ═══════════ Gallery ═══════════ */
.gallery-section {
  padding: 0 24px 120px;
}

.gallery-grid {
  display: block;
}

.gallery-card {
  width: 100%;
  display: block;
  margin-bottom: 36px;

  &:last-child {
    margin-bottom: 0;
  }
}

.gallery-img {
  width: 100%;
  height: auto;
  display: block;
  border-radius: 12px;
}

/* Tablet+: 3-column gallery */
@media (min-width: 768PX) {
  .gallery-section {
    padding: 0 24px 120px;
  }

  .gallery-grid {
    display: flex;
    flex-wrap: wrap;
  }

  .gallery-card {
    width: calc(33.333% - 24px);
    margin: 0 12px 36px;
  }
}

@media (min-width: 981PX) {
  .gallery-section {
    padding: 0 0 120px;
  }

  .gallery-grid {
    padding: 0 7%;
    gap: 24px;
  }

  .gallery-card {
    width: calc(33.333% - 24px);
    margin: 0 12px 48px;
  }
}

/* ═══════════ CTA Section ═══════════ */
.cta-section {
  margin: 0 0 80px;
  padding: 80px 40px 60px;
  background: linear-gradient(170deg, rgba(0, 113, 187, 0.2) 0%, rgba(0, 174, 255, 0.1) 100%);
  border: 2px solid $border;
  text-align: center;
}

.cta-heading {
  font-size: 42px;       /* renders ~20px */
  font-weight: 300;
  color: #fff;
  margin: 0 0 32px;
  letter-spacing: 4px;

  strong { font-weight: 800; }
}

.cta-subheading {
  font-size: 38px;       /* renders ~18px */
  font-weight: 400;
  color: $textSecondary;
  line-height: 1.5;
  margin: 0 auto 48px;
  max-width: 840px;
}

.cta-btn {
  display: inline-block;
  padding: 28px 64px;
  border-radius: 100px;
  font-size: 46px;       /* renders ~22px */
  font-weight: 700;
  cursor: pointer;
  border: none;
  background: linear-gradient(345deg, #0071bb 0%, #00aeff 100%);
  color: #fff;

  &:active { transform: scale(0.97); }
}

.cta-separator {
  width: 100%;
  height: 2px;
  background: rgba(255, 255, 255, 0.15);
  margin: 60px 0 24px;
}

.cta-spacer {
  height: 24px;
}

.cta-logos {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 40px;
  flex-wrap: wrap;
}

.cta-logo-img {
  height: 80px;
  width: auto;
  object-fit: contain;
}

/* ═══════════ Footer ═══════════ */
.oem-footer {
  padding: 80px 40px 60px;
  background: #000;
  text-align: left;
  color: rgba(255, 255, 255, 0.7);
  font-size: 34px;       /* renders ~16px */
}

.footer-grid {
  display: flex;
  flex-direction: column;
  gap: 60px;
}

.footer-logo-img {
  height: 60px;
  width: auto;
  margin-bottom: 24px;
}

.footer-tagline {
  font-size: 34px;       /* renders ~16px */
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.6;
  margin: 0 0 32px;
}

.footer-socials {
  display: flex;
  gap: 24px;
}

.social-icon-link {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #fff;
  color: #000;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 34px;
  font-weight: 700;
  cursor: pointer;
}

.footer-col--thumbs {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;

  .footer-thumb {
    width: 40%;
    max-width: 260px;
    border-radius: 16px;
    overflow: hidden;

    img {
      width: 100%;
      height: auto;
      display: block;
    }
  }
}

.footer-heading {
  font-size: 36px;       /* renders ~17px */
  font-weight: 700;
  color: #fff;
  margin: 0 0 24px;
}

.footer-link-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;

  li {
    font-size: 34px;     /* renders ~16px */
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    line-height: 1.5;
  }
}

.footer-contact-list {
  list-style: none;
  padding: 0;
  margin: 0 0 28px;
  display: flex;
  flex-direction: column;
  gap: 12px;

  li {
    font-size: 34px;     /* renders ~16px */
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.5;

    strong { color: $textColor; }
  }
}

.footer-contact-btn {
  padding: 20px 48px;
  border-radius: 100px;
  font-size: 38px;       /* renders ~18px */
  font-weight: 700;
  cursor: pointer;
  border: none;
  background: $theme;
  color: #fff;

  &:active { opacity: 0.8; }
}

.footer-bottom {
  margin-top: 60px;
  padding-top: 40px;
  border-top: 2px solid rgba(255, 255, 255, 0.15);
  text-align: center;

  p {
    font-size: 32px;     /* renders ~15px */
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
  }
}

/* ═══════════ Tablet: ≥768px ═══════════ */
@media (min-width: 768PX) {
  .hero-section {
    padding: 160px 5% 100px;
  }

  .hero-title {
    font-size: 80px;     /* renders ~38px */
    padding: 0 5%;
  }

  .breadcrumb {
    font-size: 42px;     /* renders ~20px */
    padding: 0 5%;
  }

  .about-section {
    padding: 100px 5% 80px;
  }

  .pre-header { font-size: 42px; }
  .about-heading { font-size: 66px; }  /* renders ~32px */
  .about-text { font-size: 42px; }     /* renders ~20px */

  .custom-table thead th {
    font-size: 38px;
    letter-spacing: 8px;
    img { max-height: 110px; max-width: 240px; }
  }
  .custom-table tbody td { font-size: 40px; }

  .brand-links-list {
    flex-direction: row;
    justify-content: center;
    gap: 16px;

    li { font-size: 50px; }
  }

  .gallery-card {
    width: calc(33.333% - 16px);
    margin: 0 8px 16px;
  }

  .cta-section { padding: 100px 80px 80px; }
  .cta-heading { font-size: 46px; }
  .cta-subheading { font-size: 42px; }

  .footer-grid {
    flex-direction: row;
    flex-wrap: wrap;
  }

  .footer-col--brand { width: 100%; }
  .footer-col--thumbs { width: 45%; }
  .footer-col--links { width: 25%; }
  .footer-col--contact { width: 25%; }

  .oem-footer { padding: 80px 50px 60px; }
}

/* ═══════════ Desktop: ≥981px ═══════════ */
@media (min-width: 981PX) {
  .hero-section {
    padding: 10% 7% 6%;
  }

  .hero-title {
    font-size: 108px;    /* renders ~52px */
    padding: 0 7%;
  }

  .breadcrumb {
    font-size: 50px;     /* renders ~24px */
    padding: 0 7%;
  }

  .about-section {
    padding: 160px 7% 100px;
  }

  .pre-header {
    font-size: 46px;
    margin-bottom: 32px;
  }

  .about-heading {
    font-size: 78px;     /* renders ~37px */
  }

  .about-text {
    font-size: 46px;     /* renders ~22px */
  }

  .about-cta {
    font-size: 42px;
    margin-top: 48px;
  }

  .custom-table thead th {
    font-size: 42px;
    letter-spacing: 10px;
    img { max-height: 118px; max-width: 270px; }
  }
  .custom-table tbody td { font-size: 46px; }

  .brand-links-list {
    gap: 24px;
    li { font-size: 58px; }
  }

  .cta-section { padding: 120px 120px 80px; }
  .cta-heading { font-size: 50px; }
  .cta-subheading { font-size: 46px; }
  .cta-btn { padding: 36px 96px; font-size: 58px; }
  .cta-logo-img { height: 104px; }

  .oem-footer { padding: 120px 80px 60px; }
  .footer-logo-img { height: 72px; }
  .footer-heading { font-size: 42px; }
  .footer-tagline,
  .footer-link-list li,
  .footer-contact-list li { font-size: 38px; }
  .footer-bottom p { font-size: 34px; }
  .footer-contact-btn { font-size: 42px; padding: 24px 56px; }
}
</style>
