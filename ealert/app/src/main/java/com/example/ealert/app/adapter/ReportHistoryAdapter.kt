package com.example.ealert.adapter

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.example.ealert.R
import com.example.ealert.app.response.ReportHistory

class ReportHistoryAdapter : ListAdapter<ReportHistory, ReportHistoryAdapter.ReportViewHolder>(ReportDiffCallback()) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ReportViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_report_history, parent, false)
        return ReportViewHolder(view)
    }

    override fun onBindViewHolder(holder: ReportViewHolder, position: Int) {
        val report = getItem(position)
        holder.bind(report)
    }

    class ReportViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val tvUsername: TextView = itemView.findViewById(R.id.tvUsername)
        private val tvEmergencyType: TextView = itemView.findViewById(R.id.tvEmergencyType)
        private val tvSubject: TextView = itemView.findViewById(R.id.tvSubject)
        private val tvDate: TextView = itemView.findViewById(R.id.tvDate)
        private val tvStatus: TextView = itemView.findViewById(R.id.tvStatus)
        private val tvLocation: TextView = itemView.findViewById(R.id.tvLocation)
        private val tvCoordinates: TextView = itemView.findViewById(R.id.tvCoordinates)
        private val tvClientInquiry: TextView = itemView.findViewById(R.id.tvClientInquiry)

        fun bind(report: ReportHistory) {
            tvUsername.text = report.username
            tvEmergencyType.text = report.emergencyType
            tvSubject.text = report.subjectReport
            tvDate.text = report.reportDate
            tvStatus.text = report.status
            tvLocation.text = report.location
            tvCoordinates.text = "${report.latitude}, ${report.longitude}"
            tvClientInquiry.text = report.clientInquiry

            // Set status color
            val statusColor = when (report.status.lowercase()) {
                "pending" -> android.R.color.holo_red_light
                "in-progress" -> android.R.color.holo_orange_light
                "resolved" -> android.R.color.holo_green_light
                "cancelled" -> android.R.color.darker_gray
                else -> android.R.color.black
            }
            tvStatus.setTextColor(itemView.context.resources.getColor(statusColor))
        }
    }
}

class ReportDiffCallback : DiffUtil.ItemCallback<ReportHistory>() {
    override fun areItemsTheSame(oldItem: ReportHistory, newItem: ReportHistory): Boolean {
        return oldItem.id == newItem.id
    }

    override fun areContentsTheSame(oldItem: ReportHistory, newItem: ReportHistory): Boolean {
        return oldItem == newItem
    }
}