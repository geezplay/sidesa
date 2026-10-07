# KATA PENGANTAR {-}

Puji syukur kehadirat Tuhan Yang Maha Esa atas segala rahmat dan karunia-Nya sehingga laporan yang berjudul **"Rancang Bangun Sistem Informasi Pelayanan Administrasi Desa/Kelurahan Berbasis Web (SIADESA)"** dapat diselesaikan dengan baik.

Laporan ini disusun sebagai salah satu syarat pemenuhan tugas mata kuliah Implementasi Perangkat Lunak. Sistem SIADESA dikembangkan untuk mendigitalisasi proses pelayanan administrasi desa/kelurahan, mulai dari pengajuan surat oleh masyarakat, verifikasi oleh perangkat desa, persetujuan oleh kepala desa, hingga penerbitan dokumen resmi dengan validasi QR Code.

Penulis menyadari bahwa laporan ini tidak dapat terselesaikan tanpa bantuan dari berbagai pihak. Oleh karena itu, penulis mengucapkan terima kasih kepada dosen pengampu mata kuliah Implementasi Perangkat Lunak serta semua pihak yang telah memberikan dukungan dalam penyelesaian laporan ini.

Penulis menyadari bahwa laporan ini masih jauh dari sempurna. Oleh karena itu, kritik dan saran yang membangun sangat diharapkan untuk perbaikan di masa mendatang. Semoga laporan ini dapat memberikan manfaat bagi pembaca.

```{=typst}
#v(0.8cm)
#align(right)[
  Oktober 2026

  #v(1cm)
  Tim Penyusun
]
#pagebreak()
#outline(
  title: [#align(center)[#text(size: 14pt, weight: "bold")[DAFTAR ISI]]],
  depth: 3,
)
#context {
  if opt-daftar-gambar {
    let imgs = query(figure.where(kind: image))
    if imgs.len() > 0 {
      pagebreak()
      outline(
        title: align(center)[#text(size: 14pt, weight: "bold")[DAFTAR GAMBAR]],
        target: figure.where(kind: image),
      )
    }
  }
  if opt-daftar-tabel {
    let tbls = query(figure.where(kind: table))
    if tbls.len() > 0 {
      pagebreak()
      outline(
        title: align(center)[#text(size: 14pt, weight: "bold")[DAFTAR TABEL]],
        target: figure.where(kind: table),
      )
    }
  }
}
#pagebreak()
#set page(numbering: "1")
#counter(page).update(1)
```

```{=openxml}
<w:p><w:pPr><w:jc w:val="right"/><w:spacing w:before="454"/></w:pPr><w:r><w:t>Oktober 2026</w:t></w:r></w:p>
<w:p><w:pPr><w:jc w:val="right"/><w:spacing w:before="567"/></w:pPr><w:r><w:t>Tim Penyusun</w:t></w:r></w:p>
```
